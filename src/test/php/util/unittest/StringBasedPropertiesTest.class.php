<?php namespace util\unittest;

use util\Properties;

class StringBasedPropertiesTest extends AbstractPropertiesTest {

  /**
   * Create a new properties object from a string source
   *
   * @param  string $source
   * @param  ?string $charset
   * @return util.Properties
   */
  protected function newPropertiesFrom(string $source, $charset= null): Properties {
    return (new Properties())->load($source, $charset);
  }
}