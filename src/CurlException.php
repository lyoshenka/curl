<?php

namespace lyoshenka;

class CurlException extends \Exception
{
  protected $errno, $error, $info;

  public function __construct($curlHandle, \Exception $previous = null)
  {
    $this->errno  = curl_errno($curlHandle);
    $this->error  = curl_error($curlHandle) ?: curl_strerror($this->errno);
    $this->info   = curl_getinfo($curlHandle);

    if (is_resource($curlHandle))
    {
      curl_close($curlHandle);
    }

    parent::__construct($this->error, $this->errno, $previous);
  }
}