<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\Module;
use lang\{ClassLoader, ElementNotFoundException};
use unittest\{Assert, Expect, Test};

class ModuleTest {
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
  #[After]
  public function tearDown() {
    foreach ($this->registered as $module) {
      Module::remove($module);
    }
  }

  /** @return void */
  #[Before]
  public function setUp() {
    $this->cl= ClassLoader::getDefault();
  }

  #[Test]
  public function can_create() {
    new Module('xp-framework/test', $this->cl);
  }

  #[Test]
  public function name() {
    Assert::equals('xp-framework/test', (new Module('xp-framework/test', $this->cl))->name());
  }

  #[Test]
  public function classLoader() {
    Assert::equals($this->cl, (new Module('xp-framework/test', $this->cl))->classLoader());
  }

  #[Test]
  public function equals_same() {
    Assert::equals(new Module('xp-framework/test', $this->cl), new Module('xp-framework/test', $this->cl));
  }

  #[Test]
  public function does_not_equal_module_with_different_name() {
    Assert::notEquals(new Module('xp-framework/a', $this->cl), new Module('xp-framework/b', $this->cl));
  }

  #[Test]
  public function string_representation() {
    Assert::equals(
      'lang.reflect.Module<xp-framework/test@lang.ClassLoader>',
      (new Module('xp-framework/test', $this->cl))->toString()
    );
  }

  #[Test]
  public function loaded_returns_false_when_no_module_registered() {
    Assert::false(Module::loaded('@@non-existant@@'));
  }

  #[Test]
  public function loaded_returns_true_for_register_module() {
    $module= new Module('xp-framework/loaded1', $this->cl);
    $this->register($module);
    Assert::true(Module::loaded($module->name()));
  }

  #[Test]
  public function forName_returns_registered_module() {
    $module= new Module('xp-framework/loaded2', $this->cl);
    $this->register($module);
    Assert::equals($module, Module::forName($module->name()));
  }

  #[Test, Expect(['class' => ElementNotFoundException::class, 'withMessage' => 'No module "@@non-existant@@" declared'])]
  public function forName_throws_exception_when_no_module_registered() {
    Module::forName('@@non-existant@@');
  }

  #[Test]
  public function removes_registered_module() {
    $module= new Module('xp-framework/loaded1', $this->cl);
    Module::register($module);
    Module::remove($module);
    Assert::false(Module::loaded($module->name()));
  }
}