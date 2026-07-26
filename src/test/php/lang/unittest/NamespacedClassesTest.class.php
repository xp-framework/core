<?php namespace lang\unittest;

use lang\ClassLoader;
use test\{Assert, Before, Test};

class NamespacedClassesTest {

  /** Helper to load class from the lang.unittest package */
  private function loadClass($name) {
    return ClassLoader::getDefault()->loadClass("lang.unittest.{$name}");
  }

  #[Test]
  public function namespacedClassLiteral() {
    Assert::equals(
      NamespacedClass::class, 
      $this->loadClass('NamespacedClass')->literal()
    );
  }

  #[Test]
  public function packageOfNamespacedClass() {
    Assert::equals(
      'lang.unittest',
      $this->loadClass('NamespacedClass')->packageName()
    );
  }

  #[Test]
  public function namespacedClassUsingUnqualified() {
    Assert::instance(
      Name::class,
      $this->loadClass('NamespacedClassUsingUnqualified')->newInstance()->newName()
    );
  }

  #[Test]
  public function namespacedClassUsingQualified() {
    Assert::instance(
      NamespacedClass::class,
      $this->loadClass('NamespacedClassUsingQualified')->newInstance()->getNamespacedClass()
    );
  }

  #[Test]
  public function namespacedClassUsingQualifiedUnloaded() {
    Assert::instance(
      UnloadedNamespacedClass::class,
      $this->loadClass('NamespacedClassUsingQualifiedUnloaded')->newInstance()->getNamespacedClass()
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
    Assert::equals('lang.unittest', typeof($i)->packageName());
  }

  #[Test]
  public function generics() {
    $v= create('new lang.unittest.Nullable<lang.unittest.NamespacedClass>');
    Assert::true(typeof($v)->isGeneric());
  }
}