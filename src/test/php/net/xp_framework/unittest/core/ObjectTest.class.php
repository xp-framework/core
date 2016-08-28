<?php namespace net\xp_framework\unittest\core;

use lang\XPClass;
use lang\Object;
use lang\Error;

/**
 * Tests the lang.Object class
 *
 * @see  xp://lang.Object
 */
class ObjectTest extends \unittest\TestCase {

  #[@test]
  public function is_declared_with_qualified_name() {
    $this->assertTrue(class_exists(Object::class, false));
  }

  #[@test]
  public function does_not_have_a_constructor() {
    $this->assertFalse(XPClass::forName('lang.Object')->hasConstructor());
  }

  #[@test]
  public function does_not_have_a_parent_class() {
    $this->assertNull(XPClass::forName('lang.Object')->getParentClass());
  }

  #[@test]
  public function implements_the_lang_Generic_interface() {
    $this->assertEquals([XPClass::forName('lang.Generic')], XPClass::forName('lang.Object')->getInterfaces());
  }

  #[@test]
  public function xp_typeOf_returns_fully_qualified_class_name() {
    $this->assertEquals('lang.Object', \xp::typeOf(new Object()));
  }

  #[@test]
  public function hashCode_method_returns_uniqid() {
    $this->assertTrue((bool)preg_match('/^[0-9a-z\.]+\.[0-9a-z\.]+$/', (new Object())->hashCode()));
  }

  #[@test]
  public function an_object_is_equal_to_itself() {
    $o= new Object();
    $this->assertTrue($o->equals($o));
  }

  #[@test]
  public function an_object_is_not_equal_to_another_object() {
    $this->assertFalse((new Object())->equals(new Object()));
  }

  #[@test]
  public function an_object_is_not_equal_to_a_primitive() {
    $this->assertFalse((new Object())->equals(0));
  }

  #[@test]
  public function getClass_returns_XPClass_object() {
    $this->assertEquals(XPClass::forName('lang.Object'), (new Object())->getClass());
  }

  #[@test]
  public function toString_returns_fully_qualified_class_name_and_its_hash_code() {
    $o= new Object();
    $this->assertEquals("lang.Object {\n  __id => \"".$o->hashCode()."\"\n}", $o->toString());
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method .+::undefMethod\(\)/')]
  public function calling_undefined_methods_raises_an_error() {
    (new Object())->undefMethod();
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method .+::undefMethod\(\)/')]
  public function calling_undefined_static_methods_raises_an_error() {
    Object::undefMethod();
  }
}