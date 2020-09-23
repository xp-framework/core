<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;

abstract class FieldsTest extends TestCase {
  use TypeDefinition;

  /**
   * Defines a field inside an anonymous type
   *
   * @param  string $decl Field declaration
   * @param  int $modifiers
   * @return lang.reflect.Field
   */
  protected function field($decl, $modifiers= '') {
    return $this->type('{ '.$decl.' }', ['modifiers' => $modifiers])->getField('fixture');
  }
}
