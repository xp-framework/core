<?php namespace net\xp_framework\unittest\reflection;

use lang\IllegalArgumentException;
use lang\IllegalAccessException;
use lang\XPClass;

class MethodExceptionTypesTest extends MethodsTest {

  #[@test]
  public function thrown_exceptions_are_empty_by_default() {
    $this->assertEquals([], $this->method('public function fixture() { }')->getExceptionTypes());
  }

  #[@test]
  public function thrown_exception_names_are_empty_by_default() {
    $this->assertEquals([], $this->method('public function fixture() { }')->getExceptionNames());
  }

  #[@test]
  public function thrown_exception_via_compact_apidoc() {
    $this->assertEquals(
      [new XPClass(IllegalAccessException::class)],
      $this->method('/** @throws lang.IllegalAccessException */ public function fixture() { }')->getExceptionTypes()
    );
  }

  #[@test]
  public function thrown_exception_name_via_compact_apidoc() {
    $this->assertEquals(
      ['lang.IllegalAccessException'],
      $this->method('/** @throws lang.IllegalAccessException */ public function fixture() { }')->getExceptionNames()
    );
  }

  #[@test]
  public function thrown_exceptions_via_apidoc() {
    $this->assertEquals(
      [new XPClass(IllegalAccessException::class), new XPClass(IllegalArgumentException::class)],
      $this->method('
        /**
         * @throws lang.IllegalAccessException
         * @throws lang.IllegalArgumentException
         */
        public function fixture() { }
      ')->getExceptionTypes()
    );
  }

  #[@test]
  public function thrown_exception_names_via_apidoc() {
    $this->assertEquals(
      ['lang.IllegalAccessException', 'lang.IllegalArgumentException'],
      $this->method('
        /**
         * @throws lang.IllegalAccessException
         * @throws lang.IllegalArgumentException
         */
        public function fixture() { }
      ')->getExceptionNames()
    );
  }
}