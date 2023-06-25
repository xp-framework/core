<?php namespace lang\unittest;

use lang\{ElementNotFoundException, XPClass};
use test\{Assert, Expect, Test, Values};

class AnnotationTest {

  /** @return lang.XPClass */
  private function annotated() { return XPClass::forName('lang.unittest.AnnotatedClass'); }

  #[Test]
  public function annotatedHasNoAnnotations() {
    Assert::false(typeof($this)->getMethod('annotated')->hasAnnotations());
  }

  #[Test]
  public function thisMethodHasAnnotations() {
    Assert::true(typeof($this)->getMethod('thisMethodHasAnnotations')->hasAnnotations());
  }

  #[Test]
  public function simpleAnnotationExists() {
    Assert::true($this->annotated()->getMethod('simple')->hasAnnotation('simple'));
  }

  #[Test]
  public function simpleAnnotationValue() {
    Assert::equals(null, $this->annotated()->getMethod('simple')->getAnnotation('simple'));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function getAnnotationForMethodWithout() {
    typeof($this)->getMethod('annotated')->getAnnotation('any');
  }

  #[Test]
  public function hasAnnotationForMethodWithout() {
    Assert::false(typeof($this)->getMethod('annotated')->hasAnnotation('any'));
  }
  
  #[Test, Expect(ElementNotFoundException::class)]
  public function getNonExistantAnnotation() {
    $this->annotated()->getMethod('simple')->getAnnotation('doesnotexist');
  }

  #[Test]
  public function hasNonExistantAnnotation() {
    Assert::false($this->annotated()->getMethod('simple')->hasAnnotation('doesnotexist'));
  }

  #[Test, Values(['one', 'two', 'three'])]
  public function multipleAnnotationsExist($annotation) {
    Assert::true($this->annotated()->getMethod('multiple')->hasAnnotation($annotation));
  }

  #[Test]
  public function multipleAnnotationsReturnedAsList() {
    Assert::equals(
      ['one' => null, 'two' => null, 'three' => null],
      $this->annotated()->getMethod('multiple')->getAnnotations()
    );
  }

  #[Test]
  public function stringAnnotationValue() {
    Assert::equals(
      'String value',
      $this->annotated()->getMethod('stringValue')->getAnnotation('strval')
    );
  }

  #[Test]
  public function hashAnnotationValue() {
    Assert::equals(
      ['key' => 'value'],
      $this->annotated()->getMethod('hashValue')->getAnnotation('config')
    );
  }

  #[Test]
  public function testMethodHasTestAnnotation() {
    Assert::true($this->annotated()->getMethod('testMethod')->hasAnnotation('test'));
  }

  #[Test]
  public function testMethodHasIgnoreAnnotation() {
    Assert::true($this->annotated()->getMethod('testMethod')->hasAnnotation('ignore'));
  }

  #[Test]
  public function testMethodsLimitAnnotation() {
    Assert::equals(
      ['time' => 0.1, 'memory' => 100],
      $this->annotated()->getMethod('testMethod')->getAnnotation('limit')
    );
  }

  #[Test]
  public function on_anonymous_class() {
    $c= new class() {

      #[Test]
      public function fixture() { }
    };

    Assert::equals(['test' => null], typeof($c)->getMethod('fixture')->getAnnotations());
  }
}