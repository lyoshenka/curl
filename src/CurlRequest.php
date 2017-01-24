<?php

namespace lyoshenka;

class CurlRequest
{
  protected $method;
  protected $url;
  protected $params  = [];
  protected $options = [
    'headers'          => [],
    'verify'           => true,
    'timeout'          => 5,
    'follow_redirects' => true,
    'user_agent'       => null,
    'proxy'            => null,
    'password'         => null,
    'cookie'           => null,
    'send_json_body'   => false,
    'retry'            => false,
  ];

  public function __construct($method = Curl::GET, $url = null, $params = [], $options = [])
  {
    $this->setMethod($method);
    $this->setUrl($url);
    $this->setParams($params);
    $this->setOptions($options);
  }

  public function setMethod($method)
  {
    $method = strtoupper($method);
    if (!in_array($method, Curl::getMethods()))
    {
      throw new \DomainException('Invalid method: "' . $method . '". Method must be one of: ' . join(', ', Curl::getMethods()));
    }
    $this->method = $method;
    return $this;
  }

  public function setUrl($url)
  {
    $this->url = $url;
    return $this;
  }

  public function setParams(array $params)
  {
    $this->params = $params;
    return $this;
  }

  public function addParams(array $params)
  {
    $this->params = array_merge($this->params, $params);
    return $this;
  }

  public function setParam($name, $value)
  {
    $this->params[$name] = $value;
    return $this;
  }

  public function setOptions(array $options)
  {
    foreach($options as $name => $value)
    {
      $this->setOption($name, $value);
    }
    return $this;
  }

  public function setOption($name, $value)
  {
    if (!array_key_exists($name, $this->options))
    {
      throw new \DomainException('Invalid option: ' . $name);
    }
    $this->options[$name] = $value;
    return $this;
  }

  public function setHeaders(array $headers)
  {
    $this->options['headers'] = $headers;
    return $this;
  }

  public function addHeaders(array $headers)
  {
    $this->options['headers'] = array_merge($this->options['headers'], $headers);
    return $this;
  }

  public function clearHeaders()
  {
    $this->options['headers'] = [];
    return $this;
  }

  public function getHeaders()
  {
    return $this->options['headers'];
  }

  public function setHeader($name, $value)
  {
    $this->options['headers'][$name] = $value;
    return $this;
  }

  public function getHeader($name)
  {
    return isset($this->options['headers'][$name]) ? $this->options['headers'][$name] : null;
  }

  protected function formatHeadersForCurl()
  {
    $headers = [];
    foreach($this->options['headers'] as $name => $value)
    {
      if (is_array($value))
      {
        foreach($value as $headerValue)
        {
          $headers[] = "$name: $headerValue";
        }
      }
      else
      {
        $headers[] = "$name: $value";
      }
    }
    return $headers;
  }

  public function send()
  {
    if (!function_exists('curl_init'))
    {
      throw new \RuntimeException('Curl is not available. Are you missing the curl extension?');
    }


    $ch = curl_init();

//    curl_setopt($ch, CURLOPT_VERBOSE, true);
//    curl_setopt($ch, CURLOPT_STDERR, fopen(sys_get_temp_dir().'/curl-debug-'.date('Ymd-His'), 'w+'));

    if ($ch === false || $ch === null)
    {
      throw new \RuntimeException('Unable to initialize cURL');
    }

    $urlWithParams = $this->url;
    if ($this->method == Curl::GET && $this->params)
    {
      $urlWithParams .= (strpos($urlWithParams, '?') === false ? '?' : '&') . http_build_query($this->params);
    }

    curl_setopt_array($ch, [
      CURLOPT_URL            => $urlWithParams,
      CURLOPT_HTTPHEADER     => $this->formatHeadersForCurl(),
      CURLOPT_RETURNTRANSFER => true,
      //      CURLOPT_FAILONERROR    => true,
      CURLOPT_FOLLOWLOCATION => $this->options['follow_redirects'],
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => $this->options['timeout'],
      CURLOPT_SSL_VERIFYPEER => $this->options['verify'],
      //      CURLOPT_SSL_VERIFYHOST => $this->options['verify'] ? 2 : 0, // php doc says to always keep this at 2 in production environments
      CURLOPT_USERAGENT      => $this->options['user_agent'],
    ]);

    if ($this->method == Curl::POST)
    {
      curl_setopt($ch, CURLOPT_POST, true);
    }
    elseif ($this->method == Curl::PUT)
    {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    }
    elseif ($this->method == Curl::DELETE)
    {
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    if (in_array($this->method, [Curl::PUT, Curl::POST, Curl::DELETE]))
    {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $this->options['send_json_body'] ? json_encode($this->params) : http_build_query($this->params));
    }

    if ($this->options['proxy'])
    {
      curl_setopt($ch, CURLOPT_PROXY, $this->options['proxy']);
      curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    }

    if ($this->options['password'])
    {
      curl_setopt($ch, CURLOPT_USERPWD, $this->options['password']);
    }

    if ($this->options['cookie'])
    {
      curl_setopt($ch, CURLOPT_COOKIE, $this->options['cookie']);
    }

    $startingResponse = false;
    $headers          = [];
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, function ($ch, $h) use (&$headers, &$startingResponse)
    {
      $value = trim($h);
      if ($value === '')
      {
        $startingResponse = true;
      }
      elseif ($startingResponse)
      {
        $startingResponse = false;
        $headers          = [$value];
      }
      else
      {
        $headers[] = $value;
      }
      return strlen($h);
    });

    $responseBody = curl_exec($ch);

    $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch))
    {
      if ($this->options['retry'] && is_numeric($this->options['retry']) && $this->options['retry'] > 0)
      {
        $this->options['retry'] -= 1;
        return $this->send($this->method, $this->url, $this->params, $this->options);
      }
      throw new CurlException($ch);
    }

    curl_close($ch);

    return new CurlResponse($statusCode, $headers, $responseBody);
  }
}
