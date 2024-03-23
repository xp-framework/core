<?php namespace util\unittest;

use io\streams\MemoryInputStream;
use util\Properties;

class StreamBasedPropertiesTest extends AbstractPropertiesTest {

  /**
   * Create a new properties object from a string source
   *
   * @param  string $source
   * @param  ?string $charset
   * @return util.Properties
   */
  protected function newPropertiesFrom(string $source, $charset= null): Properties {
    return (new Properties())->load(new MemoryInputStream($source), $charset);
  }
}