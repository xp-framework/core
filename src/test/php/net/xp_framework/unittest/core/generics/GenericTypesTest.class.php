<?php namespace net\xp_framework\unittest\core\generics;

use lang\Primitive;
use lang\GenericTypes;
use lang\XPClass;

/**
 * TestCase for lang.GenericTypes
 */
class GenericTypesTest extends \unittest\TestCase {
  protected static $filter;

  #[@beforeClass]
  public static function defineBase() {
    self::$filter= XPClass::forName('net.xp_framework.unittest.core.generics.NSFilter');
  }
  
  #[@test]
  public function newType0_returns_literal() {
    $this->assertEquals(
      'net\xp_framework\unittest\core\generics\NSFilter··þint',
      (new GenericTypes())->newType0(self::$filter, [Primitive::$INT])
    );
  }

  #[@test]
  public function newType_returns_XPClass_instance() {
    $this->assertInstanceOf(
      'lang.XPClass',
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])
    );
  }

  #[@test]
  public function newType_creates_generic_class() {
    $this->assertTrue(
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])->isGeneric()
    );
  }

  #[@test]
  public function newType_sets_generic_arguments() {
    $this->assertEquals(
      [Primitive::$INT],
      (new GenericTypes())->newType(self::$filter, [Primitive::$INT])->genericArguments()
    );
  }
}
