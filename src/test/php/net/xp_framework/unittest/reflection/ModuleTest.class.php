<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\Module;
use lang\ClassLoader;
use lang\ElementNotFoundException;

/**
 * TestCase for modules
 *
 * @see   xp://lang.ClassLoader
 */
class ModuleTest extends \unittest\TestCase {
  private $cl;
  private $registered= [];

  /**
   * Register a loader with the CL
   *
   * @param  lang.reflect.Module $module
   */
  private function register($module) {
    $this->registered[]= Module::register($module);
  }

  /** @return void */
  public function tearDown() {
    foreach ($this->registered as $module) {
      Module::remove($module);
    }
  }

  /** @return void */
  public function setUp() {
    $this->cl= ClassLoader::getDefault();
  }

  #[@test]
  public function can_create() {
    new Module('xp-framework/test', $this->cl);
  }

  #[@test]
  public function name() {
    $this->assertEquals('xp-framework/test', (new Module('xp-framework/test', $this->cl))->name());
  }

  #[@test]
  public function classLoader() {
    $this->assertEquals($this->cl, (new Module('xp-framework/test', $this->cl))->classLoader());
  }

  #[@test]
  public function equals_same() {
    $this->assertEquals(new Module('xp-framework/test', $this->cl), new Module('xp-framework/test', $this->cl));
  }

  #[@test]
  public function does_not_equal_module_with_different_name() {
    $this->assertNotEquals(new Module('xp-framework/a', $this->cl), new Module('xp-framework/b', $this->cl));
  }

  #[@test]
  public function string_representation() {
    $this->assertEquals(
      'lang.reflect.Module<xp-framework/test@lang.ClassLoader>',
      (new Module('xp-framework/test', $this->cl))->toString()
    );
  }

  #[@test]
  public function loaded_returns_false_when_no_module_registered() {
    $this->assertFalse(Module::loaded('@@non-existant@@'));
  }

  #[@test]
  public function loaded_returns_true_for_register_module() {
    $module= new Module('xp-framework/loaded1', $this->cl);
    $this->register($module);
    $this->assertTrue(Module::loaded($module->name()));
  }

  #[@test]
  public function forName_returns_registered_module() {
    $module= new Module('xp-framework/loaded2', $this->cl);
    $this->register($module);
    $this->assertEquals($module, Module::forName($module->name()));
  }

  #[@test, @expect(
  #  class= ElementNotFoundException::class,
  #  withMessage= 'No module "@@non-existant@@" declared'
  #)]
  public function forName_throws_exception_when_no_module_registered() {
    Module::forName('@@non-existant@@');
  }

  #[@test]
  public function removes_registered_module() {
    $module= new Module('xp-framework/loaded1', $this->cl);
    Module::register($module);
    Module::remove($module);
    $this->assertFalse(Module::loaded($module->name()));
  }

  #[@test]
  public function initialize() {
    $module= new Module('xp-framework/loaded1', $this->cl, [
      'initialize' => function(...$args) use(&$invoked) { $invoked[]= $args; }
    ]);

    $invoked= null;
    Module::register($module);
    Module::remove($module);
    $this->assertEquals([[]], $invoked);
  }

  #[@test]
  public function finalize() {
    $module= new Module('xp-framework/loaded1', $this->cl, [
      'finalize' => function(...$args) use(&$invoked) { $invoked[]= $args; }
    ]);

    $invoked= null;
    Module::register($module);
    Module::remove($module);
    $this->assertEquals([[]], $invoked);
  }

  #[@test]
  public function member() {
    $module= new Module('xp-framework/loaded1', $this->cl, [
      'member'   => 0,
      'finalize' => function(...$args) use(&$invoked) { $invoked[]= $this->member + 1; }
    ]);

    $invoked= null;
    Module::register($module);
    Module::remove($module);
    $this->assertEquals([1], $invoked);
  }

  #[@test]
  public function method() {
    $module= new Module('xp-framework/loaded1', $this->cl, [
      'method'   => function($arg) { return strtolower($arg); },
      'finalize' => function(...$args) use(&$invoked) { $invoked[]= $this->method('Test'); }
    ]);

    $invoked= null;
    Module::register($module);
    Module::remove($module);
    $this->assertEquals(['test'], $invoked);
  }
}
