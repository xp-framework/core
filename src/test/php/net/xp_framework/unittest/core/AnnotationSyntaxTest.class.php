<?php namespace net\xp_framework\unittest\core;

use lang\codedom\AnnotationSyntax;
use lang\types\String;
use lang\types\Long;

/**
 * Integration test for lang.codedom package
 */
class AnnotationSyntaxTest extends \unittest\TestCase {
  const CONSTANT = 6100;
  private static $STATIC = 'Test';

  #[@test]
  public function annotation_without_value() {
    $this->assertEquals(
      [null => ['test' => null]],
      (new AnnotationSyntax())->parse('[@test]')->resolve($this->getClassName())
    );
  }

  #[@test]
  public function target_annotation_without_value() {
    $this->assertEquals(
      ['param' => ['test' => null]],
      (new AnnotationSyntax())->parse('[@$param: test]')->resolve($this->getClassName())
    );
  }

  #[@test]
  public function two_annotations_without_value() {
    $this->assertEquals(
      [null => ['test' => null, 'ignore' => null]],
      (new AnnotationSyntax())->parse('[@test, @ignore]')->resolve($this->getClassName())
    );
  }

  #[@test, @values([
  #  ['1.5', 1.5], ['-1.5', -1.5], ['+1.5', +1.5],
  #  ['1', 1], ['0', 0], ['-6100', -6100], ['+6100', +6100],
  #  ['true', true], ['false', false], ['null', null],
  #  ['""', ''], ["''", ''], ['"Test"', 'Test'], ["'Test'", 'Test']
  #])]
  public function annotation_with_primitive_values($literal, $value) {
    $this->assertEquals(
      [null => ['test' => $value]],
      (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName())
    );
  }

  #[@test, @values([
  #  ['[]', []], ['[1, 2, 3]', [1, 2, 3]],
  #  ['array()', []], ['array(1, 2, 3)', [1, 2, 3]]
  #])]
  public function annotation_with_arrays($literal, $value) {
    $this->assertEquals(
      [null => ['test' => $value]],
      (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName())
    );
  }

  #[@test, @values([
  #  ['["color" => "green"]', ['color' => 'green']],
  #  ['["a" => "b", "c" => "d"]', ['a' => 'b', 'c' => 'd']],
  #  ['array("a" => "b", "c" => "d")', ['a' => 'b', 'c' => 'd']]
  #])]
  public function annotation_with_maps($literal, $value) {
    $this->assertEquals(
      [null => ['test' => $value]],
      (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName())
    );
  }

  #[@test, @values([
  #  ['function() { }', 'function(): var'],
  #  ['function($a) { }', 'function(var): var'],
  #  ['function($a, array $b) { }', 'function(var, var[]): var']
  #])]
  public function annotation_with_closures($literal, $type) {
    $annotation= (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName());
    $this->assertInstanceOf($type, $annotation[null]['test']);
  }

  #[@test, @values([
  #  ['limit=1.5', ['limit' => 1.5]],
  #  ['limit=1.5, eta=1.0', ['limit' => 1.5, 'eta' => 1.0]],
  #  ['limit=[1, 2, 3]', ['limit' => [1, 2, 3]]],
  #  ['class="Test"', ['class' => "Test"]],
  #  ['return="Test"', ['return' => "Test"]],
  #  ['self="Test"', ['self' => "Test"]],
  #  ['implements="Test"', ['implements' => "Test"]]
  #])]
  public function annotation_with_key_value_pairs($literal, $value) {
    $this->assertEquals(
      [null => ['test' => $value]],
      (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName())
    );
  }

  #[@test, @values([
  #  'self::CONSTANT',
  #  '\net\xp_framework\unittest\core\AnnotationSyntaxTest::CONSTANT',
  #  'AnnotationSyntaxTest::CONSTANT'
  #])]
  public function annotation_with_type_constant($literal) {
    $this->assertEquals(
      [null => ['test' => self::CONSTANT]],
      (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName())
    );
  }

  #[@test, @values([
  #  'self::class',
  #  '\net\xp_framework\unittest\core\AnnotationSyntaxTest::class',
  #  'AnnotationSyntaxTest::class'
  #])]
  public function annotation_with_class_constant($literal) {
    $this->assertEquals(
      [null => ['test' => __CLASS__]],
      (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName())
    );
  }

  #[@test, @values([
  #  'self::$STATIC',
  #  '\net\xp_framework\unittest\core\AnnotationSyntaxTest::$STATIC',
  #  'AnnotationSyntaxTest::$STATIC'
  #])]
  public function annotation_with_static_member($literal) {
    $this->assertEquals(
      [null => ['test' => self::$STATIC]],
      (new AnnotationSyntax())->parse('[@test('.$literal.')]')->resolve($this->getClassName())
    );
  }

  #[@test]
  public function annotation_with_new() {
    $this->assertEquals(
      [null => ['test' => new String('Test')]],
      (new AnnotationSyntax())->parse('[@test(new String("Test"))]')->resolve($this->getClassName())
    );
  }

  #[@test]
  public function annotation_with_imported() {
    $this->assertEquals(
      [null => ['test' => new Long(6100)]],
      (new AnnotationSyntax())->parse('[@test(new Long(6100))]')->resolve($this->getClassName(), [
        'Long' => 'lang.types.Long'
      ])
    );
  }

  #[@test]
  public function annotation_with_new_and_fully_qualified_type() {
    $this->assertEquals(
      [null => ['test' => new String('Test')]],
      (new AnnotationSyntax())->parse('[@test(new \lang\types\String("Test"))]')->resolve($this->getClassName())
    );
  }
}
