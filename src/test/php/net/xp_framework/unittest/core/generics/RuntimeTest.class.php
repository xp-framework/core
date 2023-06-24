<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalArgumentException;
use unittest\{Assert, Expect, Test};

class RuntimeTest {
  private $fixture;
  
  #[Before]
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.Lookup<string, net.xp_framework.unittest.core.generics.RuntimeTest>()');
  }

  #[Test]
  public function name() {
    Assert::equals(
      'net.xp_framework.unittest.core.generics.Lookup<string,net.xp_framework.unittest.core.generics.RuntimeTest>',
      typeof($this->fixture)->getName()
    );
  }

  #[Test]
  public function literal() {
    Assert::equals(
      "net\\xp_framework\\unittest\\core\\generics\\Lookup\xb7\xb7\xfestring\xb8net\xa6xp_framework\xa6unittest\xa6core\xa6generics\xa6RuntimeTest",
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
    Assert::equals($this, $this->fixture->get('Test'));
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