<?php namespace security;

/**
 * OpenSSL utility functions
 *
 * @ext   openssl
 */
class OpenSslUtil extends \lang\Object {

  /**
   * Retrieve errors
   *
   * @return  string[] error
   */
  public static function getErrors() {
    $e= [];
    while ($msg= openssl_error_string()) {
      $e[]= $msg;
    }
    return $e;
  }
  
  /**
   * Get OpenSSL configuration file environment value
   *
   * @return  string
   */
  public function getConfiguration() {
    return getenv('OPENSSL_CONF');
  }
}
