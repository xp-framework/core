<?php namespace net\xp_framework\unittest\reflection;

use lang\{IllegalAccessException, IllegalArgumentException, XPClass};
use unittest\Assert;
use unittest\{Test, Values};

class MethodExceptionTypesTest extends MethodsTest {

  #[Test]
  public function thrown_exceptions_are_empty_by_default() {
    Assert::equals([], $this->method('public function fixture() { }')->getExceptionTypes());
  }

  #[Test]
  public function thrown_exception_names_are_empty_by_default() {
    Assert::equals([], $this->method('public function fixture() { }')->getExceptionNames());
  }

  #[Test, Values([['/** @throws lang.IllegalAccessException */'], ['/** @throws \lang\IllegalAccessException */']])]
  public function thrown_exception_via_compact_apidoc($apidoc) {
    Assert::equals(
      [new XPClass(IllegalAccessException::class)],
      $this->method($apidoc.' public function fixture() { }')->getExceptionTypes()
    );
  }

  #[Test]
  public function thrown_exception_name_via_compact_apidoc() {
    Assert::equals(
      ['lang.IllegalAccessException'],
      $this->method('/** @throws lang.IllegalAccessException */ public function fixture() { }')->getExceptionNames()
    );
  }

  #[Test]
  public function thrown_exceptions_via_apidoc() {
    Assert::equals(
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

  #[Test]
  public function thrown_exception_names_via_apidoc() {
    Assert::equals(
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