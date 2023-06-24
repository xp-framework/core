<?php namespace net\xp_framework\unittest\core\generics;

use lang\{GenericTypes, Primitive, XPClass};
use unittest\Assert;
use unittest\{BeforeClass, Test, TestCase};

/**
 * TestCase for lang.GenericTypes
 */
class GenericTypesTest {
  private static $filter;

  #[BeforeClass]
  public static function defineBase() {
    self::$filter= XPClass::forName('net.xp_framework.unittest.core.generics.ArrayFilter');
  }
  
  #[Test]
  public function newType0_returns_literal() {
    Assert::equals(
      "net\\xp_framework\\unittest\\core\\generics\\ArrayFilter\xb7\xb7\xfeint",
      (new GenericTypes())->newType0(self::$filter, [Primitive::$INT])
    );
  }

  #[Test]
  public function newType_returns_XPClass_instance() {
    Assert::instance(
      XPClass::class,
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])
    );
  }

  #[Test]
  public function newType_creates_generic_class() {
    Assert::true(
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])->isGeneric()
    );
  }

  #[Test]
  public function newType_sets_generic_arguments() {
    Assert::equals(
      [Primitive::$INT],
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])->genericArguments()
    );
  }
}