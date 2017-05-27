<?php namespace lang\reflect;

use lang\{IClassLoader, ElementNotFoundException, Value};

/**
 * Represents a module
 *
 * @test  xp://net.xp_framework.unittest.reflection.ModuleTest
 */
class Module implements Value {
  public static $INCOMPLETE= false;
  public static $registered= [];

  /**
   * Creates a new module
   *
   * @param  string $name
   * @param  lang.IClassLoader $classLoader
   */
  public function __construct($name, IClassLoader $classLoader) {
    $this->name= $name;
    $this->classLoader= $classLoader;
  }

  /** @return string */
  public function name() { return $this->name; }

  /** @return lang.IClassLoader */
  public function classLoader() { return $this->classLoader; }

  /**
   * Initialize this module. Template method, override in subclasses!
   *
   * @return void
   */
  public function initialize() { }

  /**
   * Finalize this module. Template method, override in subclasses!
   *
   * @return void
   */
  public function finalize() { }

  /** Compares this module to another value */
  public function compareTo($value): int {
    if (!($value instanceof self)) return 1;
    if (0 !== ($c= $value->name <=> $this->name)) return $c;
    if (0 !== ($c= $value->classLoader->compareTo($this->classLoader))) return $c;
    return 0;
  }

  /** Returns a hashcode for this module */
  public function hashCode(): string {
    return 'M['.$this->name.'@'.$this->classLoader->hashCode();
  }

  /** Returns a string representation of this module */
  public function toString(): string {
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