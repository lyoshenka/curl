<?php

namespace lyoshenka;

class CurlException extends \Exception
{
  protected $errno, $error, $info, $handle;

  public function __construct($curlHandle, \Exception $previous = null)
  {
    $this->handle = $curlHandle;
    $this->errno  = curl_errno($curlHandle);
    $this->error  = curl_error($curlHandle);
    $this->info   = curl_getinfo($curlHandle);

    parent::__construct($this->error, $this->errno, $previous);
  }
}