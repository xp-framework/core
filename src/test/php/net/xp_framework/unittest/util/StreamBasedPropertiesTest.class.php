<?php namespace net\xp_framework\unittest\util;

use io\streams\MemoryInputStream;
use util\Properties;

class StreamBasedPropertiesTest extends AbstractPropertiesTest {

  /**
   * Create a new properties object from a string source
   *
   * @param   string source
   * @return  util.Properties
   */
  protected function newPropertiesFrom(string $source): Properties {
    return (new Properties())->load(new MemoryInputStream($source));
  }
}