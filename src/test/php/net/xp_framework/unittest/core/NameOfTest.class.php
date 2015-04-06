<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;

/**
 * Tests nameof() functionality
 *
 */
class NameOfTest extends \unittest\TestCase {

  #[@test]
  public function of_instance() {
    $this->assertEquals('net.xp_framework.unittest.core.NameOfTest', nameof($this));
  }

  #[@test]
  public function of_short_class() {
    $this->assertEquals('net.xp_framework.unittest.core.ShortClass', nameof(new ShortClass()));
  }

  #[@test]
  public function of_packaged_class() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.PackagedClass',
      nameof(XPClass::forName('net.xp_framework.unittest.core.PackagedClass')->newInstance())
    );
  }

  #[@test]
  public function of_php_instance() {
    $this->assertEquals('Exception', nameof(new \Exception('Test')));
  }
}