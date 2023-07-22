<?php namespace lang\unittest;

use lang\ClassFormatException;
use lang\reflect\ClassParser;
use lang\unittest\fixture\Namespaced;
use test\{Assert, Expect, Interceptors, Test, Value, Values};

class AnnotationParsingTest extends AbstractAnnotationParsingTest {
  const CONSTANT= 'constant';

  public static $exposed = 'exposed';
  protected static $hidden = 'hidden';
  private static $internal = 'internal';

  /**
   * Helper
   *
   * @param   string $input
   * @param   [:var] $imports
   * @return  [:var]
   */
  protected function parse($input, $imports= []) {
    try {
      return (new ClassParser())->parseAnnotations($input, nameof($this), array_merge($imports, [
        'Namespaced' => 'lang.unittest.fixture.Namespaced'
      ]));
    } finally {
      \xp::gc(); // Strip deprecation warning
    }
  }

  #[Test]
  public function no_value() {
    Assert::equals(
      [0 => ['hello' => null], 1 => []],
      $this->parse("#[@hello]")
    );
  }

  #[Test]
  public function sq_string_value() {
    Assert::equals(
      [0 => ['hello' => 'World'], 1 => []],
      $this->parse("#[@hello('World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_equals_sign() {
    Assert::equals(
      [0 => ['hello' => 'World=Welt'], 1 => []],
      $this->parse("#[@hello('World=Welt')]")
    );
  }

  #[Test]
  public function sq_string_value_with_at_sign() {
    Assert::equals(
      [0 => ['hello' => '@World'], 1 => []],
      $this->parse("#[@hello('@World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_annotation() {
    Assert::equals(
      [0 => ['hello' => '@hello("World")'], 1 => []],
      $this->parse("#[@hello('@hello(\"World\")')]")
    );
  }

  #[Test]
  public function sq_string_value_with_double_quotes() {
    Assert::equals(
      [0 => ['hello' => 'said "he"'], 1 => []],
      $this->parse("#[@hello('said \"he\"')]")
    );
  }

  #[Test]
  public function sq_string_value_with_escaped_single_quotes() {
    Assert::equals(
      [0 => ['hello' => "said 'he'"], 1 => []],
      $this->parse("#[@hello('said \'he\'')]")
    );
  }

  #[Test]
  public function dq_string_value() {
    Assert::equals(
      [0 => ['hello' => 'World'], 1 => []],
      $this->parse('#[@hello("World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_single_quote() {
    Assert::equals(
      [0 => ['hello' => 'Beck\'s'], 1 => []],
      $this->parse('#[@hello("Beck\'s")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escaped_double_quotes() {
    Assert::equals(
      [0 => ['hello' => 'said "he"'], 1 => []],
      $this->parse('#[@hello("said \"he\"")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escape_sequence() {
    Assert::equals(
      [0 => ['hello' => "World\n"], 1 => []],
      $this->parse('#[@hello("World\n")]')
    );
  }

  #[Test]
  public function dq_string_value_with_at_sign() {
    Assert::equals(
      [0 => ['hello' => '@World'], 1 => []],
      $this->parse('#[@hello("@World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_annotation() {
    Assert::equals(
      [0 => ['hello' => '@hello(\'World\')'], 1 => []],
      $this->parse('#[@hello("@hello(\'World\')")]')
    );
  }

  #[Test]
  public function int_value() {
    Assert::equals(
      [0 => ['answer' => 42], 1 => []],
      $this->parse('#[@answer(42)]')
    );
  }

  #[Test]
  public function double_value() {
    Assert::equals(
      [0 => ['version' => 3.5], 1 => []],
      $this->parse('#[@version(3.5)]')
    );
  }

  #[Test]
  public function multi_value_using_short_array() {
    Assert::equals(
      [0 => ['xmlmapping' => ['hw_server', 'server']], 1 => []],
      $this->parse("#[@xmlmapping(['hw_server', 'server'])]")
    );
  }

  #[Test]
  public function short_array_value() {
    Assert::equals(
      [0 => ['versions' => [3.4, 3.5]], 1 => []],
      $this->parse('#[@versions([3.4, 3.5])]')
    );
  }

  #[Test]
  public function short_array_value_with_nested_arrays() {
    Assert::equals(
      [0 => ['versions' => [[3], [4]]], 1 => []],
      $this->parse('#[@versions([[3], [4]])]')
    );
  }

  #[Test]
  public function short_array_value_with_strings_containing_braces() {
    Assert::equals(
      [0 => ['versions' => ['(3..4]']], 1 => []],
      $this->parse('#[@versions(["(3..4]"])]')
    );
  }

  #[Test]
  public function bool_true_value() {
    Assert::equals(
      [0 => ['supported' => true], 1 => []],
      $this->parse('#[@supported(true)]')
    );
  }

  #[Test]
  public function bool_false_value() {
    Assert::equals(
      [0 => ['supported' => false], 1 => []],
      $this->parse('#[@supported(false)]')
    );
  }

  #[Test]
  public function short_map_value() {
    Assert::equals(
      [0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']], 1 => []],
      $this->parse("#[@colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[Test]
  public function multi_line_annotation() {
    Assert::equals(
      [0 => ['interceptors' => ['classes' => [
        'lang.unittest.FirstInterceptor',
        'lang.unittest.SecondInterceptor',
      ]]], 1 => []],
      $this->parse("
        #[@interceptors(['classes' => [
          'lang.unittest.FirstInterceptor',
          'lang.unittest.SecondInterceptor',
        ]])]
      ")
    );
  }

  #[Test]
  public function simple_XPath_annotation() {
    Assert::equals(
      [0 => ['fromXml' => ['xpath' => '/parent/child/@attribute']], 1 => []],
      $this->parse("#[@fromXml(['xpath' => '/parent/child/@attribute'])]")
    );
  }

  #[Test]
  public function complex_XPath_annotation() {
    Assert::equals(
      [0 => ['fromXml' => ['xpath' => '/parent[@attr="value"]/child[@attr1="val1" and @attr2="val2"]']], 1 => []],
      $this->parse("#[@fromXml(['xpath' => '/parent[@attr=\"value\"]/child[@attr1=\"val1\" and @attr2=\"val2\"]'])]")
    );
  }

  #[Test]
  public function string_with_equal_signs() {
    Assert::equals(
      [0 => ['permission' => 'rn=login, rt=config'], 1 => []],
      $this->parse("#[@permission('rn=login, rt=config')]")
    );
  }

  #[Test]
  public function string_assigned_without_whitespace() {
    Assert::equals(
      [0 => ['arg' => ['name' => 'verbose', 'short' => 'v']], 1 => []],
      $this->parse("#[@arg(['name' => 'verbose', 'short' => 'v'])]")
    );
  }

  #[Test]
  public function multiple_values_with_strings_and_equal_signs() {
    Assert::equals(
      [0 => ['permission' => ['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']]], 1 => []],
      $this->parse("#[@permission(['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']])]")
    );
  }

  #[Test]
  public function unittest_annotation() {
    Assert::equals(
      [0 => ['test' => NULL, 'ignore' => NULL, 'limit' => ['time' => 0.1, 'memory' => 100]], 1 => []],
      $this->parse("#[@test, @ignore, @limit(['time' => 0.1, 'memory' => 100])]")
    );
  }

  #[Test]
  public function overloaded_annotation() {
    Assert::equals(
      [0 => ['overloaded' => ['signatures' => [['string'], ['string', 'string']]]], 1 => []],
      $this->parse('#[@overloaded(["signatures" => [["string"], ["string", "string"]]])]')
    );
  }

  #[Test]
  public function overloaded_annotation_spanning_multiple_lines() {
    Assert::equals(
      [0 => ['overloaded' => ['signatures' => [['string'], ['string', 'string']]]], 1 => []],
      $this->parse(
        "#[@overloaded(['signatures' => [\n".
        "  ['string'],\n".
        "  ['string', 'string']\n".
        "]])]"
      )
    );
  }

  #[Test]
  public function webmethod_with_parameter_annotations() {
    Assert::equals(
      [
        0 => ['webmethod' => ['verb' => 'GET', 'path' => '/greet/{name}']],
        1 => ['$name' => ['path' => null], '$greeting' => ['param' => null]]
      ],
      $this->parse('#[@webmethod(["verb" => "GET", "path" => "/greet/{name}"]), @$name: path, @$greeting: param]')
    );
  }

  #[Test]
  public function map_value_with_short_syntax() {
    Assert::equals(
      [0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']], 1 => []],
      $this->parse("#[@colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[Test]
  public function short_array_syntax_as_value() {
    Assert::equals(
      [0 => ['permissions' => ['rn=login, rt=config', 'rn=admin, rt=config']], 1 => []],
      $this->parse("#[@permissions(['rn=login, rt=config', 'rn=admin, rt=config'])]")
    );
  }

  #[Test]
  public function short_array_syntax_as_key() {
    Assert::equals(
      [0 => ['permissions' => ['names' => ['rn=login, rt=config', 'rn=admin, rt=config']]], 1 => []],
      $this->parse("#[@permissions(['names' => ['rn=login, rt=config', 'rn=admin, rt=config']])]")
    );
  }

  #[Test]
  public function nested_short_array_syntax() {
    Assert::equals(
      [0 => ['values' => [[1, 1], [2, 2], [3, 3]]], 1 => []],
      $this->parse("#[@values([[1, 1], [2, 2], [3, 3]])]")
    );
  }

  #[Test]
  public function nested_short_array_syntax_as_key() {
    Assert::equals(
      [0 => ['test' => ['values' => [[1, 1], [2, 2], [3, 3]]]], 1 => []],
      $this->parse("#[@test(['values' => [[1, 1], [2, 2], [3, 3]]])]")
    );
  }

  #[Test]
  public function negative_and_positive_floats_inside_array() {
    Assert::equals(
      [0 => ['values' => [0.0, -1.5, +1.5]], 1 => []],
      $this->parse("#[@values([0.0, -1.5, +1.5])]")
    );
  }

  #[Test]
  public function class_instance_value() {
    Assert::equals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[@value(new Name("hello"))]')
    );
  }

  #[Test]
  public function imported_class_instance_value() {
    Assert::equals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[@value(new Name("hello"))]', ['Name' => 'lang.unittest.Name'])
    );
  }

  #[Test]
  public function fully_qualified_class_instance_value() {
    Assert::equals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[@value(new \lang\unittest\Name("hello"))]')
    );
  }

  #[Test]
  public function fully_qualified_not_loaded_class() {
    $this->parse('#[@value(new \lang\unittest\NotLoaded())]');
  }


  #[Test]
  public function class_constant_via_self() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(self::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_parent() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(parent::PARENTS_CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_classname() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(\lang\unittest\AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_imported_classname() {
    Assert::equals(
      [0 => ['value' => 'namespaced'], 1 => []],
      $this->parse('#[@value(Namespaced::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_self_in_map() {
    Assert::equals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[@map(["key" => self::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_classname_in_map() {
    Assert::equals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[@map(["key" => AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname_in_map() {
    Assert::equals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[@map(["key" => \lang\unittest\AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_public_static_member() {
    Assert::equals(
      [0 => ['value' => 'exposed'], 1 => []],
      $this->parse('#[@value(self::$exposed)]')
    );
  }

  #[Test]
  public function parent_public_static_member() {
    Assert::equals(
      [0 => ['value' => 'exposed'], 1 => []],
      $this->parse('#[@value(parent::$parentsExposed)]')
    );
  }

  #[Test]
  public function class_protected_static_member() {
    Assert::equals(
      [0 => ['value' => 'hidden'], 1 => []],
      $this->parse('#[@value(self::$hidden)]')
    );
  }

  #[Test]
  public function parent_protected_static_member() {
    Assert::equals(
      [0 => ['value' => 'hidden'], 1 => []],
      $this->parse('#[@value(parent::$parentsHidden)]')
    );
  }

  #[Test]
  public function class_private_static_member() {
    Assert::equals(
      [0 => ['value' => 'internal'], 1 => []],
      $this->parse('#[@value(self::$internal)]')
    );
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Cannot access private static field .+AbstractAnnotationParsingTest::\$parentsInternal/')]
  public function parent_private_static_member() {
    $this->parse('#[@value(parent::$parentsInternal)]');
  }

  #[Test]
  public function closure() {
    $annotation= $this->parse('#[@value(function() { return true; })]');
    Assert::instance('Closure', $annotation[0]['value']);
  }

  #[Test]
  public function closures() {
    $annotation= $this->parse('#[@values([
      function() { return true; },
      function() { return false; }
    ])]');
    Assert::instance('Closure[]', $annotation[0]['values']);
  }

  #[Test]
  public function short_closure() {
    $annotation= $this->parse('#[@value(fn() => true)]');
    Assert::instance('Closure', $annotation[0]['value']);
  }

  #[Test, Values(['#[Value(fn() => new Name("Test"))]', '#[Value(function() { return new Name("Test"); })]',])]
  public function imports_in_closures($closure) {
    $annotation= $this->parse($closure, ['Name' => 'lang.unittest.Name']);
    Assert::instance(Name::class, $annotation[0]['value']());
  }
}