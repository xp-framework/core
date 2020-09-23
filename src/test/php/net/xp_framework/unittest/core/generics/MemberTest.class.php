<?php namespace net\xp_framework\unittest\core\generics;

use unittest\{Ignore, Test, TestCase};

/**
 * TestCase for member access
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ListOf
 */
class MemberTest extends TestCase {
  protected $fixture= null;

  /** @return void */
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.ListOf<string>', 'Hello', 'World');
  }

  #[Test]
  public function readAccess() {
    $this->assertEquals(['Hello', 'World'], $this->fixture->elements);
  }

  #[Test, Ignore('Behaviour not defined')]
  public function readNonExistant() {
    $this->fixture->nonexistant;
  }

  #[Test]
  public function writeAccess() {
    $this->fixture->elements= ['Hello', 'Wörld'];
    $this->assertEquals(['Hello', 'Wörld'], $this->fixture->elements);
  }

  #[Test, Ignore('Behaviour not defined')]
  public function writeNonExistant() {
    $this->fixture->nonexistant= true;
  }
}