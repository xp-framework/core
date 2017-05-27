<?php namespace net\xp_framework\unittest\core;

use lang\Object;
use lang\reflect\Package;
use util\collections\Vector;

/**
 * TestCase for XP Framework's namespaces support
 *
 * @see   https://github.com/xp-framework/xp-framework/issues/132
 * @see   https://github.com/xp-framework/rfc/issues/222
 * @see   xp://net.xp_framework.unittest.core.NamespacedClass
 * @see   php://namespaces
 */
class NamespacedClassesTest extends \unittest\TestCase {
  protected static $package;

  #[@beforeClass]
  public static function initializePackage() {
    self::$package= Package::forName('net.xp_framework.unittest.core');
  }

  #[@test]
  public function namespacedClassLiteral() {
    $this->assertEquals(
      NamespacedClass::class, 
      self::$package->loadClass('NamespacedClass')->literal()
    );
  }

  #[@test]
  public function packageOfNamespacedClass() {
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      self::$package->loadClass('NamespacedClass')->getPackage()
    );
  }

  #[@test]
  public function namespacedClassUsingUnqualified() {
    $this->assertInstanceOf(
      Object::class,
      self::$package->loadClass('NamespacedClassUsingUnqualified')->newInstance()->newObject()
    );
  }

  #[@test]
  public function namespacedClassUsingQualified() {
    $this->assertInstanceOf(
      NamespacedClass::class,
      self::$package->loadClass('NamespacedClassUsingQualified')->newInstance()->getNamespacedClass()
    );
  }

  #[@test]
  public function namespacedClassUsingQualifiedUnloaded() {
    $this->assertInstanceOf(
      UnloadedNamespacedClass::class,
      self::$package->loadClass('NamespacedClassUsingQualifiedUnloaded')->newInstance()->getNamespacedClass()
    );
  }

  #[@test]
  public function newInstanceOnNamespacedClass() {
    $i= new class() extends NamespacedClass {};
    $this->assertInstanceOf(NamespacedClass::class, $i);
  }

  #[@test]
  public function packageOfNewInstancedNamespacedClass() {
    $i= newinstance(NamespacedClass::class, []);
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      typeof($i)->getPackage()
    );
  }

  #[@test]
  public function generics() {
    $v= create('new net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.NamespacedClass>');
    $this->assertTrue(typeof($v)->isGeneric());
  }
}
