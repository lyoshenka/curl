<?php

namespace lyoshenka;

class Curl
{
  const
    GET    = 'GET',
    POST   = 'POST',
    PUT    = 'PUT',
    DELETE = 'DELETE';

  /**
   * @return array
   */
  public static function getMethods()
  {
    return [static::GET, static::POST, static::PUT, static::DELETE];
  }

  /**
   * @param string $url
   * @param array  $params
   * @param array  $options
   *
   * @return CurlResponse
   */
  public static function get($url, $params = [], $options = [])
  {
    return static::request(static::GET, $url, $params, $options);
  }

  /**
   * @param string $url
   * @param array  $params
   * @param array  $options
   *
   * @return CurlResponse
   */
  public static function post($url, $params = [], $options = [])
  {
    return static::request(static::POST, $url, $params, $options);
  }

  /**
   * @param string $url
   * @param array  $params
   * @param array  $options
   *
   * @return CurlResponse
   */
  public static function put($url, $params = [], $options = [])
  {
    return static::request(static::PUT, $url, $params, $options);
  }

  /**
   * @param string $url
   * @param array  $params
   * @param array  $options
   *
   * @return CurlResponse
   */
  public static function delete($url, $params = [], $options = [])
  {
    return static::request(static::DELETE, $url, $params, $options);
  }

  /**
   * @param string $method
   * @param string $url
   * @param array  $params
   * @param array  $options
   *
   * @return CurlResponse
   */
  public static function request($method, $url, $params = [], $options = [])
  {
    return (new CurlRequest($method, $url, $params, $options))->send();
  }

  /**
   * @return CurlRequest
   */
  public static function init()
  {
    return new CurlRequest();
  }
}

