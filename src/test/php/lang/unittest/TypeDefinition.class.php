<?php namespace lang\unittest;

use lang\{ClassLoader, Value};

trait TypeDefinition {
  private static $fixtures= [];

  /**
   * Defines an anonymous type
   *
   * @param  string $decl Type declaration
   * @param  [:var] $definition
   * @return lang.XPClass
   */
  protected function type($decl= '', array $definition= []) {
    if (!isset(self::$fixtures[$decl])) {
      $defaults= [
        'modifiers'  => '',
        'kind'       => 'class',
        'extends'    => null,
        'implements' => [],
        'use'        => [CompareTo::class],
        'imports'    => [Value::class => null]
      ];
      self::$fixtures[$decl]= ClassLoader::defineType(
        get_class($this).sizeof(self::$fixtures),
        array_merge($defaults, $definition),
        $decl
      );
    }
    return self::$fixtures[$decl];
  }
}