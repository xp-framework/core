<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalArgumentException;

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://collections.Lookup
 */
class RuntimeTest extends \unittest\TestCase {
  private $fixture;
  
  /**
   * Creates fixture, a Lookup with string and TestCase as component types.
   *
   * @return void
   */  
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
  }

  #[@test]
  public function name() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>',
      typeof($this->fixture)->getName()
    );
  }

  #[@test]
  public function literal() {
    $this->assertEquals(
      'net\\xp_framework\\unittest\\core\\generics\\Lookup··þstring¸unittest¦TestCase',
      typeof($this->fixture)->literal()
    );
  }

  #[@test]
  public function putStringAndThis() {
    $this->fixture->put('Test', $this);
  }

  #[@test]
  public function putAndGetRoundTrip() {
    $this->fixture->put('Test', $this);
    $this->assertEquals($this, $this->fixture->get('Test'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function keyTypeIncorrect() {
    $this->fixture->put(1, $this);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function valueTypeIncorrect() {
    $this->fixture->put('Test', new class() { });
  }
}
