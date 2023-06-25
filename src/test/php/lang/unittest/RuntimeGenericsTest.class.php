<?php namespace lang\unittest;

use lang\IllegalArgumentException;
use test\{Assert, Before, Expect, Test};

class RuntimeGenericsTest {
  private $fixture;
  
  #[Before]
  public function setUp() {
    $this->fixture= create('new lang.unittest.Lookup<string, lang.unittest.RuntimeGenericsTest>()');
  }

  #[Test]
  public function name() {
    Assert::equals(
      'lang.unittest.Lookup<string,lang.unittest.RuntimeGenericsTest>',
      typeof($this->fixture)->getName()
    );
  }

  #[Test]
  public function literal() {
    Assert::equals(
      "lang\\unittest\\Lookup\xb7\xb7\xfestring\xb8lang\xa6unittest\xa6RuntimeGenericsTest",
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