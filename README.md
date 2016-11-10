# lyoshenka/curl

A simple wrapper around Curl to make it easy to use.

No more `curl_init()`, no more struggling to get response headers, etc.

## Two ways to use it

### The functional way

    echo \lyoshenka\curl::get('http://httpbin.org/user-agent', [], ['headers' => ['User-Agent' => 'nice!']]);

### The fluent OO way

    echo \lyoshenka\curl::new()
      ->setMethod('GET')
      ->setUrl('http://httpbin.org/user-agent')
      ->setHeader('User-Agent', 'also nice!')
      ->send()
      ->getBody();