<?php namespace lang\unittest;

use lang\reflect\Package;
use unittest\{Assert, Before, Test};
use util\collections\Vector;

class NamespacedClassesTest {
  protected $package;

  #[Before]
  public function initializePackage() {
    $this->package= Package::forName('lang.unittest');
  }

  #[Test]
  public function namespacedClassLiteral() {
    Assert::equals(
      NamespacedClass::class, 
      $this->package->loadClass('NamespacedClass')->literal()
    );
  }

  #[Test]
  public function packageOfNamespacedClass() {
    Assert::equals(
      Package::forName('lang.unittest'),
      $this->package->loadClass('NamespacedClass')->getPackage()
    );
  }

  #[Test]
  public function namespacedClassUsingUnqualified() {
    Assert::instance(
      Name::class,
      $this->package->loadClass('NamespacedClassUsingUnqualified')->newInstance()->newName()
    );
  }

  #[Test]
  public function namespacedClassUsingQualified() {
    Assert::instance(
      NamespacedClass::class,
      $this->package->loadClass('NamespacedClassUsingQualified')->newInstance()->getNamespacedClass()
    );
  }

  #[Test]
  public function namespacedClassUsingQualifiedUnloaded() {
    Assert::instance(
      UnloadedNamespacedClass::class,
      $this->package->loadClass('NamespacedClassUsingQualifiedUnloaded')->newInstance()->getNamespacedClass()
    );
  }

  #[Test]
  public function newInstanceOnNamespacedClass() {
    $i= new class() extends NamespacedClass {};
    Assert::instance(NamespacedClass::class, $i);
  }

  #[Test]
  public function packageOfNewInstancedNamespacedClass() {
    $i= newinstance(NamespacedClass::class, []);
    Assert::equals(
      Package::forName('lang.unittest'),
      typeof($i)->getPackage()
    );
  }

  #[Test]
  public function generics() {
    $v= create('new lang.unittest.Nullable<lang.unittest.NamespacedClass>');
    Assert::true(typeof($v)->isGeneric());
  }
}