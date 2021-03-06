<?php

namespace lyoshenka;

class CurlResponse
{
  protected $status;
  protected $headers = [];
  protected $body;

  public function __construct($status, array $headers, $body)
  {
    $this->status  = (int)$status;
    $this->headers = $headers;
    $this->body    = $body;
  }

  /**
   * @return int
   */
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * @return array
   */
  public function getHeaders()
  {
    return $this->headers;
  }

  /**
   * @param string $name
   *
   * @return string|null
   */
  public function getHeader($name)
  {
    return isset($this->headers[$name]) ? $this->headers[$name] : null;
  }

  /**
   * @return string
   */
  public function getBody()
  {
    return $this->body;
  }

  /**
   * @return bool
   */
  public function isJson()
  {
    return in_array($this->getHeader('Content-Type'), ['application/json', 'application/javascript']);
  }

  /**
   * @return mixed
   */
  public function getJson()
  {
    return json_decode($this->body, true);
  }

  /**
   * @return mixed|string
   */
  public function getBodySmart()
  {
    return $this->isJson() ? $this->getJson() : $this->getBody();
  }

  public function __toString()
  {
    return $this->getBody();
  }
}
