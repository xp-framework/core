<?php namespace net\xp_framework\unittest\core;

use lang\reflect\Package;
use net\xp_framework\unittest\Name;
use unittest\{BeforeClass, Test};
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

  #[BeforeClass]
  public static function initializePackage() {
    self::$package= Package::forName('net.xp_framework.unittest.core');
  }

  #[Test]
  public function namespacedClassLiteral() {
    $this->assertEquals(
      NamespacedClass::class, 
      self::$package->loadClass('NamespacedClass')->literal()
    );
  }

  #[Test]
  public function packageOfNamespacedClass() {
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      self::$package->loadClass('NamespacedClass')->getPackage()
    );
  }

  #[Test]
  public function namespacedClassUsingUnqualified() {
    $this->assertInstanceOf(
      Name::class,
      self::$package->loadClass('NamespacedClassUsingUnqualified')->newInstance()->newName()
    );
  }

  #[Test]
  public function namespacedClassUsingQualified() {
    $this->assertInstanceOf(
      NamespacedClass::class,
      self::$package->loadClass('NamespacedClassUsingQualified')->newInstance()->getNamespacedClass()
    );
  }

  #[Test]
  public function namespacedClassUsingQualifiedUnloaded() {
    $this->assertInstanceOf(
      UnloadedNamespacedClass::class,
      self::$package->loadClass('NamespacedClassUsingQualifiedUnloaded')->newInstance()->getNamespacedClass()
    );
  }

  #[Test]
  public function newInstanceOnNamespacedClass() {
    $i= new class() extends NamespacedClass {};
    $this->assertInstanceOf(NamespacedClass::class, $i);
  }

  #[Test]
  public function packageOfNewInstancedNamespacedClass() {
    $i= newinstance(NamespacedClass::class, []);
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.core'),
      typeof($i)->getPackage()
    );
  }

  #[Test]
  public function generics() {
    $v= create('new net.xp_framework.unittest.core.generics.Nullable<net.xp_framework.unittest.core.NamespacedClass>');
    $this->assertTrue(typeof($v)->isGeneric());
  }
}