<?php namespace net\xp_framework\unittest\reflection;

use lang\ElementNotFoundException;

class MethodAnnotationsTest extends MethodsTest {

  #[@test]
  public function has_annotations_when_absent() {
    $this->assertFalse($this->method('public function fixture() { }')->hasAnnotations());
  }

  #[@test]
  public function has_annotations_when_present() {
    $this->assertTrue($this->method("#[@test]\npublic function fixture() { }")->hasAnnotations());
  }

  #[@test]
  public function annotations_are_empty_by_default() {
    $this->assertEquals([], $this->method('public function fixture() { }')->getAnnotations());
  }

  #[@test]
  public function test_annotation() {
    $this->assertEquals(
      ['test' => null],
      $this->method("#[@test]\npublic function fixture() { }")->getAnnotations()
    );
  }

  #[@test]
  public function two_annotations() {
    $this->assertEquals(
      ['test' => null, 'limit' => 20],
      $this->method("#[@test, @limit(20)]\npublic function fixture() { }")->getAnnotations()
    );
  }

  #[@test]
  public function has_annotation_when_absent() {
    $this->assertFalse($this->method('public function fixture() { }')->hasAnnotation('test'));
  }

  #[@test]
  public function has_annotation_for_existant_annotation() {
    $this->assertTrue($this->method("#[@test]\npublic function fixture() { }")->hasAnnotation('test'));
  }

  #[@test]
  public function has_annotation_for_non_existant_annotation() {
    $this->assertFalse($this->method("#[@test]\npublic function fixture() { }")->hasAnnotation('@@nonexistant@@'));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function get_annotation_when_absent() {
    $this->method('public function fixture() { }')->getAnnotation('test');
  }

  #[@test]
  public function get_annotation_for_existant_annotation() {
    $this->assertNull($this->method("#[@test]\npublic function fixture() { }")->getAnnotation('test'));
  }

  #[@test]
  public function get_annotation_for_existant_annotation_with_value() {
    $this->assertEquals(20, $this->method("#[@limit(20)]\npublic function fixture() { }")->getAnnotation('limit'));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function get_annotation_for_non_existant_annotation() {
    $this->method("#[@test]\npublic function fixture() { }")->getAnnotation('@@nonexistant@@');
  }
}