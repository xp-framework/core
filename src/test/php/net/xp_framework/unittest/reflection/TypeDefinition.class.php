<?php namespace net\xp_framework\unittest\reflection;

use lang\Value;
use lang\ClassLoader;

trait TypeDefinition {
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
        'extends'    => null,
        'implements' => [],
        'use'        => [CompareTo::class],
        'imports'    => [Value::class => null]
      ];
      self::$fixtures[$decl]= ClassLoader::defineType(get_class($this).sizeof(self::$fixtures), $definition, $decl);
    }
    return self::$fixtures[$decl];
  }
}