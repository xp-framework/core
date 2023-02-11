<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalArgumentException;
use unittest\{Expect, Test, TestCase};

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://collections.Lookup
 */
class RuntimeTest extends TestCase {
  private $fixture;
  
  /**
   * Creates fixture, a Lookup with string and TestCase as component types.
   *
   * @return void
   */  
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.Lookup<string, unittest.TestCase>()');
  }

  #[Test]
  public function name() {
    $this->assertEquals(
      'net.xp_framework.unittest.core.generics.Lookup<string,unittest.TestCase>',
      typeof($this->fixture)->getName()
    );
  }

  #[Test]
  public function literal() {
    $this->assertEquals(
      "net\\xp_framework\\unittest\\core\\generics\\Lookup\xabstring\xb8unittest\x98TestCase\xbb",
      typeof($this->fixture)->literal()
    );
  }

  #[Test]
  public function putStringAndThis() {
    $this->fixture->put('Test', $this);
  }

  #[Test]
  public function putAndGetRoundTrip() {
    $this->fixture->put('Test', $this);
    $this->assertEquals($this, $this->fixture->get('Test'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function keyTypeIncorrect() {
    $this->fixture->put(1, $this);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function valueTypeIncorrect() {
    $this->fixture->put('Test', new class() { });
  }
}