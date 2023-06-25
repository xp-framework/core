<?php namespace lang\unittest;

abstract class FieldsTest {
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