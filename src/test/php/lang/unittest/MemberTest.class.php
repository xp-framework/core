<?php namespace lang\unittest;

use test\{Assert, Before, Ignore, Test};

class MemberTest {
  protected $fixture= null;

  #[Before]
  public function setUp() {
    $this->fixture= create('new lang.unittest.ListOf<string>', 'Hello', 'World');
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