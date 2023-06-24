<?php namespace net\xp_framework\unittest\reflection;

use lang\ElementNotFoundException;
use unittest\{Assert, Expect, Test};

class MethodAnnotationsTest extends MethodsTest {

  #[Test]
  public function has_annotations_when_absent() {
    Assert::false($this->method('public function fixture() { }')->hasAnnotations());
  }

  #[Test]
  public function has_annotations_when_present() {
    Assert::true($this->method("#[Test]\npublic function fixture() { }")->hasAnnotations());
  }

  #[Test]
  public function annotations_are_empty_by_default() {
    Assert::equals([], $this->method('public function fixture() { }')->getAnnotations());
  }

  #[Test]
  public function test_annotation() {
    Assert::equals(
      ['test' => null],
      $this->method("#[Test]\npublic function fixture() { }")->getAnnotations()
    );
  }

  #[Test]
  public function two_annotations() {
    Assert::equals(
      ['test' => null, 'limit' => 20],
      $this->method("#[Test, Limit(20)]\npublic function fixture() { }")->getAnnotations()
    );
  }

  #[Test]
  public function has_annotation_when_absent() {
    Assert::false($this->method('public function fixture() { }')->hasAnnotation('test'));
  }

  #[Test]
  public function has_annotation_for_existant_annotation() {
    Assert::true($this->method("#[Test]\npublic function fixture() { }")->hasAnnotation('test'));
  }

  #[Test]
  public function has_annotation_for_non_existant_annotation() {
    Assert::false($this->method("#[Test]\npublic function fixture() { }")->hasAnnotation('@@nonexistant@@'));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function get_annotation_when_absent() {
    $this->method('public function fixture() { }')->getAnnotation('test');
  }

  #[Test]
  public function get_annotation_for_existant_annotation() {
    Assert::null($this->method("#[Test]\npublic function fixture() { }")->getAnnotation('test'));
  }

  #[Test]
  public function get_annotation_for_existant_annotation_with_value() {
    Assert::equals(20, $this->method("#[Limit(20)]\npublic function fixture() { }")->getAnnotation('limit'));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function get_annotation_for_non_existant_annotation() {
    $this->method("#[Test]\npublic function fixture() { }")->getAnnotation('@@nonexistant@@');
  }

  #[Test]
  public function parameter_annotation() {
    Assert::true($this->method("public function fixture(\n#[Inject]\n\$arg) { }")
      ->getParameter(0)
      ->hasAnnotation('inject')
    );
  }
}