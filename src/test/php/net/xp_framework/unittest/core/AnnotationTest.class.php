<?php namespace net\xp_framework\unittest\core;

use lang\{ElementNotFoundException, XPClass};
use unittest\{Expect, Test, Values};

/**
 * Tests the XP Framework's annotations
 *
 * @see   xp://net.xp_framework.unittest.core.AnnotatedClass
 * @see   xp://lang.reflect.Routine
 * @see   xp://lang.reflect.XPClass
 * @see   rfc://0016
 */
class AnnotationTest extends \unittest\TestCase {

  /** @return lang.XPClass */
  public function annotated() {
    $class= XPClass::forName('net.xp_framework.unittest.core.AnnotatedClass');

    // Trigger annotation parsing, swallowing warnings
    $class->getAnnotations();
    \xp::gc();
    return $class;
  }

  #[Test]
  public function setUpMethodHasNoAnnotations() {
    $this->assertFalse(typeof($this)->getMethod('setUp')->hasAnnotations());
  }

  #[Test]
  public function thisMethodHasAnnotations() {
    $this->assertTrue(typeof($this)->getMethod('thisMethodHasAnnotations')->hasAnnotations());
  }

  #[Test]
  public function simpleAnnotationExists() {
    $this->assertTrue($this->annotated()->getMethod('simple')->hasAnnotation('simple'));
  }

  #[Test]
  public function simpleAnnotationValue() {
    $this->assertEquals(null, $this->annotated()->getMethod('simple')->getAnnotation('simple'));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function getAnnotationForMethodWithout() {
    typeof($this)->getMethod('setUp')->getAnnotation('any');
  }

  #[Test]
  public function hasAnnotationForMethodWithout() {
    $this->assertFalse(typeof($this)->getMethod('setUp')->hasAnnotation('any'));
  }
  
  #[Test, Expect(ElementNotFoundException::class)]
  public function getNonExistantAnnotation() {
    $this->annotated()->getMethod('simple')->getAnnotation('doesnotexist');
  }

  #[Test]
  public function hasNonExistantAnnotation() {
    $this->assertFalse($this->annotated()->getMethod('simple')->hasAnnotation('doesnotexist'));
  }

  #[Test, Values(['one', 'two', 'three'])]
  public function multipleAnnotationsExist($annotation) {
    $this->assertTrue($this->annotated()->getMethod('multiple')->hasAnnotation($annotation));
  }

  #[Test]
  public function multipleAnnotationsReturnedAsList() {
    $this->assertEquals(
      ['one' => null, 'two' => null, 'three' => null],
      $this->annotated()->getMethod('multiple')->getAnnotations()
    );
  }

  #[Test]
  public function stringAnnotationValue() {
    $this->assertEquals(
      'String value',
      $this->annotated()->getMethod('stringValue')->getAnnotation('strval')
    );
  }

  #[Test]
  public function hashAnnotationValue() {
    $this->assertEquals(
      ['key' => 'value'],
      $this->annotated()->getMethod('hashValue')->getAnnotation('config')
    );
  }

  /** @deprecated */
  #[Test]
  public function keyValuePairAnnotationValue() {
    $this->assertEquals(
      ['key' => 'value'],
      $this->annotated()->getMethod('keyValuePair')->getAnnotation('config')
    );
  }

  #[Test]
  public function testMethodHasTestAnnotation() {
    $this->assertTrue($this->annotated()->getMethod('testMethod')->hasAnnotation('test'));
  }

  #[Test]
  public function testMethodHasIgnoreAnnotation() {
    $this->assertTrue($this->annotated()->getMethod('testMethod')->hasAnnotation('ignore'));
  }

  #[Test]
  public function testMethodsLimitAnnotation() {
    $this->assertEquals(
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

    $this->assertEquals(['test' => null], typeof($c)->getMethod('fixture')->getAnnotations());
  }
}