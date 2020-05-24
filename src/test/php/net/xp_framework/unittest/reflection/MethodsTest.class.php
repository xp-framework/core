<?php namespace net\xp_framework\unittest\reflection;

abstract class MethodsTest extends \unittest\TestCase {
  use TypeDefinition;

  /**
   * Defines a method inside an anonymous type
   *
   * @param  string $decl Method declaration
   * @param  int $modifiers
   * @return lang.reflect.Method
   */
  protected function method($decl, $modifiers= '') {
    return $this->type('{ '.$decl.' }', ['modifiers' => $modifiers])->getMethod('fixture');
  }
}