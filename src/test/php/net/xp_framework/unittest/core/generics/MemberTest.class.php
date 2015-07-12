<?php namespace net\xp_framework\unittest\core\generics;

/**
 * TestCase for member access
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ListOf
 */
class MemberTest extends \unittest\TestCase {
  protected $fixture= null;

  /**
   * Creates fixture
   *
   */
  public function setUp() {
    $this->fixture= create('new net.xp_framework.unittest.core.generics.ListOf<string>', 'Hello', 'World');
  }

  #[@test]
  public function readAccess() {
    $this->assertEquals(['Hello', 'World'], $this->fixture->elements);
  }

  #[@test, @ignore('Behaviour not defined')]
  public function readNonExistant() {
    $this->fixture->nonexistant;
  }

  #[@test]
  public function writeAccess() {
    $this->fixture->elements= ['Hello', 'Wörld'];
    $this->assertEquals(['Hello', 'Wörld'], $this->fixture->elements);
  }

  #[@test, @ignore('Behaviour not defined')]
  public function writeNonExistant() {
    $this->fixture->nonexistant= true;
  }
}
