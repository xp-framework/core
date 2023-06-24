<?php namespace net\xp_framework\unittest\core\generics;

use unittest\Assert;
use unittest\{Ignore, Test, TestCase};

/**
 * TestCase for member access
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ListOf
 */
class MemberTest {
  protected $fixture= null;

  /** @return void */
  #[Before]
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.ListOf<string>', 'Hello', 'World');
  }

  #[Test]
  public function readAccess() {
    Assert::equals(['Hello', 'World'], $this->fixture->elements);
  }

  #[Test, Ignore('Behaviour not defined')]
  public function readNonExistant() {
    $this->fixture->nonexistant;
  }

  #[Test]
  public function writeAccess() {
    $this->fixture->elements= ['Hello', 'Wörld'];
    Assert::equals(['Hello', 'Wörld'], $this->fixture->elements);
  }

  #[Test, Ignore('Behaviour not defined')]
  public function writeNonExistant() {
    $this->fixture->nonexistant= true;
  }
}