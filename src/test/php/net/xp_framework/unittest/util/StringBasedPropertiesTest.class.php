<?php namespace net\xp_framework\unittest\util;

use util\Properties;

class StringBasedPropertiesTest extends AbstractPropertiesTest {

  /**
   * Create a new properties object from a string source
   *
   * @param   string source
   * @return  util.Properties
   */
  protected function newPropertiesFrom(string $source): Properties {
    return (new Properties())->load($source);
  }
}