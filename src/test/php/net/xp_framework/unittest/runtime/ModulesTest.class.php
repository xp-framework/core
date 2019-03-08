<?php namespace net\xp_framework\unittest\runtime;

use unittest\TestCase;
use xp\runtime\{Modules, ModuleNotFound};

class ModulesTest extends TestCase {

  #[@test]
  public function can_create() {
    new Modules();
  }

  #[@test]
  public function adding_a_module() {
    $fixture= new Modules();
    $fixture->add('xp-forge/sequence', 'util\data\Sequence');

    $this->assertEquals(['xp-forge/sequence' => 'util\data\Sequence'], $fixture->all());
  }

  #[@test]
  public function requiring_core_works() {
    $fixture= new Modules();
    $fixture->add('xp-framework/core', 'util\Objects');

    $fixture->require($namespace= 'test');
  }

  #[@test]
  public function requiring_php_works() {
    $fixture= new Modules();
    $fixture->add('php', 'Throwable');

    $fixture->require($namespace= 'test');
  }

  #[@test, @expect(ModuleNotFound::class)]
  public function requiring_non_existant() {
    $fixture= new Modules();
    $fixture->add('perpetuum/mobile', 'util\perpetuum\Mobile');

    $fixture->require($namespace= 'test');
  }
}