<?php namespace net\xp_framework\unittest\reflection;

use lang\Generic;
use lang\XPClass;
use lang\Type;

class MethodParametersTest extends \unittest\TestCase {

  /** @return void */
  protected function fixture0() { }

  protected function fixturex(UnknownTypeRestriction $restriction) { }

  /**
   * @param  string $a
   * @param  self $b
   * @param  lang.Generic $c
   * @param  lang.Generic $d
   * @param  var[] $e
   * @param  function(string): int $f
   * @param  bool $g
   */
  #[@$d: test('value')]
  protected function fixture($a, self $b, Generic $c, $d, array $e, callable $f, $g= true) { }

  /**
   * Returns a method for a given fixture in this class
   *
   * @param  string $name
   * @return lang.reflect.Method
   */
  protected function method($name) {
    return $this->getClass()->getMethod($name);
  }

  #[@test]
  public function zero_parameters() {
    $this->assertEquals(0, $this->method('fixture0')->numParameters());
  }

  #[@test]
  public function numParameters() {
    $this->assertEquals(7, $this->method('fixture')->numParameters());
  }

  #[@test]
  public function no_parameters() {
    $this->assertEquals([], $this->method('fixture0')->getParameters());
  }

  #[@test]
  public function parameter_names() {
    $this->assertEquals(['a', 'b', 'c', 'd', 'e', 'f', 'g'], array_map(
      function($p) { return $p->getName(); },
      $this->method('fixture')->getParameters()
    ));
  }

  #[@test]
  public function parameter_representations() {
    $repr= [
      'lang.reflect.Parameter<lang.Primitive<string> a>',
      'lang.reflect.Parameter<lang.XPClass<net.xp_framework.unittest.reflection.MethodParametersTest> b>',
      'lang.reflect.Parameter<lang.XPClass<lang.Generic> c>',
      'lang.reflect.Parameter<lang.XPClass<lang.Generic> d>',
      'lang.reflect.Parameter<lang.ArrayType<var[]> e>',
      'lang.reflect.Parameter<lang.FunctionType<function(string): int> f>',
      'lang.reflect.Parameter<lang.Primitive<bool> g= true>'
    ];

    $this->assertEquals($repr, array_map(
      function($p) { return $p->toString(); },
      $this->method('fixture')->getParameters()
    ));
  }

  #[@test, @values([-1, 0, 1])]
  public function accessing_a_parameter_via_non_existant_offset($offset) {
    $this->assertNull($this->method('fixture0')->getParameter($offset));
  }

  #[@test]
  public function unrestricted_parameter() {
    $this->assertNull($this->method('fixture')->getParameter(0)->getTypeRestriction());
  }

  #[@test]
  public function self_restricted_parameter() {
    $this->assertEquals(
      $this->getClass(),
      $this->method('fixture')->getParameter(1)->getTypeRestriction()
    );
  }

  #[@test]
  public function interface_restricted_parameter() {
    $this->assertEquals(
      XPClass::forName('lang.Generic'),
      $this->method('fixture')->getParameter(2)->getTypeRestriction()
    );
  }

  #[@test]
  public function unrestricted_interface_parameter() {
    $this->assertNull($this->method('fixture')->getParameter(3)->getTypeRestriction());
  }

  #[@test]
  public function array_restricted_parameter() {
    $this->assertEquals(
      Type::$ARRAY,
      $this->method('fixture')->getParameter(4)->getTypeRestriction()
    );
  }

  #[@test]
  public function callable_restricted_parameter() {
    $this->assertEquals(
      Type::$CALLABLE,
      $this->method('fixture')->getParameter(5)->getTypeRestriction()
    );
  }

  #[@test]
  public function unrestricted_parameter_with_default() {
    $this->assertNull($this->method('fixture')->getParameter(6)->getTypeRestriction());
  }

  #[@test, @expect('lang.ClassFormatException')]
  public function nonexistant_restriction_class_parameter() {
    $this->method('fixturex')->getParameter(0)->getTypeRestriction();
  }

  #[@test]
  public function string_primitive_typed_parameter() {
    $this->assertEquals(
      Type::forName('string'),
      $this->method('fixture')->getParameter(0)->getType()
    );
  }

  #[@test]
  public function self_typed_parameter() {
    $this->assertEquals(
      $this->getClass(),
      $this->method('fixture')->getParameter(1)->getType()
    );
  }

  #[@test]
  public function interface_typed_parameter() {
    $this->assertEquals(
      XPClass::forName('lang.Generic'),
      $this->method('fixture')->getParameter(2)->getType()
    );
  }

  #[@test]
  public function interface_typed_unrestricted_parameter() {
    $this->assertEquals(
      XPClass::forName('lang.Generic'),
      $this->method('fixture')->getParameter(3)->getType()
    );
  }

  #[@test]
  public function array_typed_parameter() {
    $this->assertEquals(
      Type::forName('var[]'),
      $this->method('fixture')->getParameter(4)->getType()
    );
  }

  #[@test]
  public function callable_typed_parameter() {
    $this->assertEquals(
      Type::forName('function(string): int'),
      $this->method('fixture')->getParameter(5)->getType()
    );
  }

  #[@test]
  public function bool_primitive_typed_parameter_with_default() {
    $this->assertEquals(
      Type::forName('bool'),
      $this->method('fixture')->getParameter(6)->getType()
    );
  }

  #[@test, @expect('lang.ClassFormatException')]
  public function nonexistant_typed_class_parameter() {
    $this->method('fixturex')->getParameter(0)->getType();
  }

  #[@test]
  public function string_primitive_typed_parameter_name() {
    $this->assertEquals(
      'string',
      $this->method('fixture')->getParameter(0)->getTypeName()
    );
  }

  #[@test]
  public function self_typed_parameter_name() {
    $this->assertEquals(
      'self',
      $this->method('fixture')->getParameter(1)->getTypeName()
    );
  }

  #[@test]
  public function interface_typed_parameter_name() {
    $this->assertEquals(
      'lang.Generic',
      $this->method('fixture')->getParameter(2)->getTypeName()
    );
  }

  #[@test]
  public function interface_typed_unrestricted_parameter_name() {
    $this->assertEquals(
      'lang.Generic',
      $this->method('fixture')->getParameter(3)->getTypeName()
    );
  }

  #[@test]
  public function array_typed_parameter_name() {
    $this->assertEquals(
      'var[]',
      $this->method('fixture')->getParameter(4)->getTypeName()
    );
  }

  #[@test]
  public function callable_typed_parameter_name() {
    $this->assertEquals(
      'function(string): int',
      $this->method('fixture')->getParameter(5)->getTypeName()
    );
  }

  #[@test]
  public function bool_primitive_typed_parameter_with_default_name() {
    $this->assertEquals(
      'bool',
      $this->method('fixture')->getParameter(6)->getTypeName()
    );
  }

  #[@test]
  public function nonexistant_typed_class_parameter_name() {
    $this->assertEquals(
      'UnknownTypeRestriction',
      $this->method('fixturex')->getParameter(0)->getTypeName()
    );
  }

  #[@test]
  public function required_parameter() {
    $this->assertFalse($this->method('fixture')->getParameter(0)->isOptional());
  }

  #[@test]
  public function optional_parameter() {
    $this->assertTrue($this->method('fixture')->getParameter(6)->isOptional());
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Parameter "a" has no default value')]
  public function required_parameter_does_not_have_default_value() {
    $this->method('fixture')->getParameter(0)->getDefaultValue();
  }

  #[@test]
  public function optional_parameters_default_value() {
    $this->assertEquals(true, $this->method('fixture')->getParameter(6)->getDefaultValue());
  }

  #[@test]
  public function annotated_parameter() {
    $this->assertTrue($this->method('fixture')->getParameter(3)->hasAnnotations());
  }

  #[@test]
  public function parameter_annotated_with_test_has_test_annotation() {
    $this->assertTrue($this->method('fixture')->getParameter(3)->hasAnnotation('test'));
  }

  #[@test]
  public function parameter_annotated_with_test_has_no_limit_annotation() {
    $this->assertFalse($this->method('fixture')->getParameter(3)->hasAnnotation('limit'));
  }

  #[@test]
  public function annotations_of_parameter_annotated_with_test() {
    $this->assertEquals(['test' => 'value'], $this->method('fixture')->getParameter(3)->getAnnotations());
  }

  #[@test]
  public function test_annotation_of_parameter_annotated_with_test() {
    $this->assertEquals('value', $this->method('fixture')->getParameter(3)->getAnnotation('test'));
  }

  #[@test]
  public function un_annotated_parameter_has_no_annotations() {
    $this->assertFalse($this->method('fixture')->getParameter(0)->hasAnnotations());
  }

  #[@test]
  public function un_annotated_parameter_annotations_are_empty() {
    $this->assertEquals([], $this->method('fixture')->getParameter(0)->getAnnotations());
  }

  #[@test, @expect(class= 'lang.ElementNotFoundException', withMessage= 'Annotation "test" does not exist')]
  public function cannot_get_test_annotation_for_un_annotated_parameter() {
    $this->method('fixture')->getParameter(0)->getAnnotation('test');
  }
}