<?php namespace net\xp_framework\unittest\reflection;

use lang\Object;
use lang\Value;
use lang\ClassLoader;

abstract class MethodsTest extends \unittest\TestCase {
  private static $fixtures= [];

  /**
   * Defines an anonymous type
   *
   * @param  string $decl Type declaration
   * @param  int $modifiers
   * @return lang.XPClass
   */
  protected function type($decl= null, $modifiers= '') {
    if (!isset(self::$fixtures[$decl])) {
      $definition= [
        'modifiers'  => $modifiers,
        'kind'       => 'class',
        'extends'    => [Object::class],
        'implements' => [],
        'use'        => [CompareTo::class],
        'imports'    => [Value::class => null]
      ];
      self::$fixtures[$decl]= ClassLoader::defineType(self::class.sizeof(self::$fixtures), $definition, $decl);
    }
    return self::$fixtures[$decl];
  }

  /**
   * Defines a method inside an anonymous type
   *
   * @param  string $decl Method declaration
   * @param  int $modifiers
   * @return lang.reflect.Method
   */
  protected function method($decl, $modifiers= '') {
    return $this->type('{ '.$decl.' }', $modifiers)->getMethod('fixture');
  }
}