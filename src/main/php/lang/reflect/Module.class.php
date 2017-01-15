<?php namespace lang\reflect;

use lang\IClassLoader;
use lang\ElementNotFoundException;

/**
 * Represents a module
 *
 * @test  xp://net.xp_framework.unittest.reflection.ModuleTest
 */
class Module extends \lang\Object {
  public static $INCOMPLETE= false;
  public static $registered= [];
  private $definition;

  /**
   * Creates a new module
   *
   * @param  string $name
   * @param  lang.IClassLoader $classLoader
   */
  public function __construct($name, IClassLoader $classLoader, $definitions= []) {
    $this->name= $name;
    $this->classLoader= $classLoader;

    // Lifecycle: Initialize and finalize, defined as NOOPs by default
    $this->definitions= ['initialize' => function() { }, 'finalize'   => function() { }];
    foreach ($definitions as $name => $definition) {
      $this->definitions[$name]= $definition->bindTo($this, $this);
    }
  }

  /** @return string */
  public function name() { return $this->name; }

  /** @return lang.IClassLoader */
  public function classLoader() { return $this->classLoader; }

  /** 
   * Call operator overloading
   *
   * @param  string $name
   * @param  var[] $arguments
   * @return var
   */
  public function __call($name, $arguments) {
    return ($this->definitions[$name])($arguments);
  }

  /**
   * Returns whether a given value equals this module
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $cmp->name === $this->name;
  }

  /**
   * Returns a string representation of this module
   *
   * @return string
   */
  public function toString() {
    return nameof($this).'<'.$this->name.'@'.$this->classLoader->toString().'>';
  }

  /**
   * Register a module. Calls module's initializer.
   *
   * @param  self $module
   * @return self
   */
  public static function register(self $module) {
    self::$registered[$module->name()]= $module;
    $module->initialize();
    return $module;
  }

  /**
   * Remove a registered module. Calls module's finalizer.
   *
   * @param  self $module
   */
  public static function remove(self $module) {
    $module->finalize();
    unset(self::$registered[$module->name()]);
  }

  /**
   * Returns whether a module is registered by a given name
   *
   * @param  string $name
   * @return bool
   */
  public static function loaded($name) {
    return isset(self::$registered[$name]);
  }

  /**
   * Retrieve a previously registered module by its name
   * 
   * @param  string $name
   * @return self
   * @throws lang.ElementNotFoundException
   */
  public static function forName($name) {
    if (!isset(self::$registered[$name])) {
      throw new ElementNotFoundException('No module "'.$name.'" declared');
    }
    return self::$registered[$name];
  }
}