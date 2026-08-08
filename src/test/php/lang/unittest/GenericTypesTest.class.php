<?php namespace lang\unittest;

use lang\{GenericTypes, Primitive, XPClass};
use test\{Assert, Before, Test};

class GenericTypesTest {
  private $filter;

  #[Before]
  public function filter() {
    $this->filter= XPClass::forName('lang.unittest.ArrayFilter');
  }
  
  #[Test]
  public function newType0_returns_literal() {
    Assert::equals(
      "lang\\unittest\\ArrayFilter\xb7\xb7\xfeint",
      (new GenericTypes())->newType0($this->filter, [Primitive::$INT])
    );
  }

  #[Test]
  public function newType_returns_XPClass_instance() {
    Assert::instance(
      XPClass::class,
      (new GenericTypes())->newType($this->filter, [Primitive::$INT])
    );
  }

  #[Test]
  public function newType_creates_generic_class() {
    Assert::true(
      (new GenericTypes())->newType($this->filter, [Primitive::$INT])->isGeneric()
    );
  }

  #[Test]
  public function newType_sets_generic_arguments() {
    Assert::equals(
      [Primitive::$INT],
      (new GenericTypes())->newType($this->filter, [Primitive::$INT])->genericArguments()
    );
  }
}