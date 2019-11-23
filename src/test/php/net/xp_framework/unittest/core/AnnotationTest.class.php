<?php namespace net\xp_framework\unittest\core;

use lang\ElementNotFoundException;
use lang\XPClass;

/**
 * Tests the XP Framework's annotations
 *
 * @see   xp://net.xp_framework.unittest.core.AnnotatedClass
 * @see   xp://lang.reflect.Routine
 * @see   xp://lang.reflect.XPClass
 * @see   rfc://0016
 */
class AnnotationTest extends \unittest\TestCase {
  private $class;

  /** @return void */
  public function setUp() {
    $this->class= XPClass::forName('net.xp_framework.unittest.core.AnnotatedClass');
  }

  #[@test]
  public function setUpMethodHasNoAnnotations() {
    $this->assertFalse(typeof($this)->getMethod('setUp')->hasAnnotations());
  }

  #[@test]
  public function thisMethodHasAnnotations() {
    $this->assertTrue(typeof($this)->getMethod('thisMethodHasAnnotations')->hasAnnotations());
  }

  #[@test]
  public function simpleAnnotationExists() {
    $this->assertTrue($this->class->getMethod('simple')->hasAnnotation('simple'));
  }

  #[@test]
  public function simpleAnnotationValue() {
    $this->assertEquals(null, $this->class->getMethod('simple')->getAnnotation('simple'));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function getAnnotationForMethodWithout() {
    typeof($this)->getMethod('setUp')->getAnnotation('any');
  }

  #[@test]
  public function hasAnnotationForMethodWithout() {
    $this->assertFalse(typeof($this)->getMethod('setUp')->hasAnnotation('any'));
  }
  
  #[@test, @expect(ElementNotFoundException::class)]
  public function getNonExistantAnnotation() {
    $this->class->getMethod('simple')->getAnnotation('doesnotexist');
  }

  #[@test]
  public function hasNonExistantAnnotation() {
    $this->assertFalse($this->class->getMethod('simple')->hasAnnotation('doesnotexist'));
  }

  #[@test, @values(['one', 'two', 'three'])]
  public function multipleAnnotationsExist($annotation) {
    $this->assertTrue($this->class->getMethod('multiple')->hasAnnotation($annotation));
  }

  #[@test]
  public function multipleAnnotationsReturnedAsList() {
    $this->assertEquals(
      ['one' => null, 'two' => null, 'three' => null],
      $this->class->getMethod('multiple')->getAnnotations()
    );
  }

  #[@test]
  public function stringAnnotationValue() {
    $this->assertEquals(
      'String value',
      $this->class->getMethod('stringValue')->getAnnotation('strval')
    );
  }

  #[@test]
  public function hashAnnotationValue() {
    $this->assertEquals(
      ['key' => 'value'],
      $this->class->getMethod('hashValue')->getAnnotation('config')
    );
  }

  /** @deprecated */
  #[@test]
  public function keyValuePairAnnotationValue() {
    $this->assertEquals(
      ['key' => 'value'],
      $this->class->getMethod('keyValuePair')->getAnnotation('config')
    );
  }

  #[@test]
  public function testMethodHasTestAnnotation() {
    $this->assertTrue($this->class->getMethod('testMethod')->hasAnnotation('test'));
  }

  #[@test]
  public function testMethodHasIgnoreAnnotation() {
    $this->assertTrue($this->class->getMethod('testMethod')->hasAnnotation('ignore'));
  }

  #[@test]
  public function testMethodsLimitAnnotation() {
    $this->assertEquals(
      ['time' => 0.1, 'memory' => 100],
      $this->class->getMethod('testMethod')->getAnnotation('limit')
    );
  }

  #[@test]
  public function on_anonymous_class() {
    $c= new class() {

      #[@test]
      public function fixture() { }
    };

    $this->assertEquals(['test' => null], typeof($c)->getMethod('fixture')->getAnnotations());
  }
}
