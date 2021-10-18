<?php namespace net\xp_framework\unittest\annotations;

use lang\ClassFormatException;
use lang\reflect\ClassParser;
use net\xp_framework\unittest\annotations\fixture\Namespaced;
use unittest\{Expect, Interceptors, Test, Value, Values};

/**
 * Tests the XP Framework's annotation parsing implementation
 *
 * @see     rfc://0016
 * @see     xp://lang.XPClass#parseAnnotations
 * @see     http://bugs.xp-framework.net/show_bug.cgi?id=38
 * @see     https://github.com/xp-framework/xp-framework/issues/14
 * @see     https://github.com/xp-framework/xp-framework/pull/56
 * @see     https://gist.github.com/1240769
 */
class AnnotationParsingTest extends AbstractAnnotationParsingTest {
  const CONSTANT = 'constant';
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
        'Namespaced' => 'net.xp_framework.unittest.annotations.fixture.Namespaced'
      ]));
    } finally {
      \xp::gc(); // Strip deprecation warning
    }
  }

  #[Test]
  public function no_value() {
    $this->assertEquals(
      [0 => ['hello' => null], 1 => []],
      $this->parse("#[@hello]")
    );
  }

  #[Test]
  public function sq_string_value() {
    $this->assertEquals(
      [0 => ['hello' => 'World'], 1 => []],
      $this->parse("#[@hello('World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_equals_sign() {
    $this->assertEquals(
      [0 => ['hello' => 'World=Welt'], 1 => []],
      $this->parse("#[@hello('World=Welt')]")
    );
  }

  #[Test]
  public function sq_string_value_with_at_sign() {
    $this->assertEquals(
      [0 => ['hello' => '@World'], 1 => []],
      $this->parse("#[@hello('@World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_annotation() {
    $this->assertEquals(
      [0 => ['hello' => '@hello("World")'], 1 => []],
      $this->parse("#[@hello('@hello(\"World\")')]")
    );
  }

  #[Test]
  public function sq_string_value_with_double_quotes() {
    $this->assertEquals(
      [0 => ['hello' => 'said "he"'], 1 => []],
      $this->parse("#[@hello('said \"he\"')]")
    );
  }

  #[Test]
  public function sq_string_value_with_escaped_single_quotes() {
    $this->assertEquals(
      [0 => ['hello' => "said 'he'"], 1 => []],
      $this->parse("#[@hello('said \'he\'')]")
    );
  }

  #[Test]
  public function dq_string_value() {
    $this->assertEquals(
      [0 => ['hello' => 'World'], 1 => []],
      $this->parse('#[@hello("World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_single_quote() {
    $this->assertEquals(
      [0 => ['hello' => 'Beck\'s'], 1 => []],
      $this->parse('#[@hello("Beck\'s")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escaped_double_quotes() {
    $this->assertEquals(
      [0 => ['hello' => 'said "he"'], 1 => []],
      $this->parse('#[@hello("said \"he\"")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escape_sequence() {
    $this->assertEquals(
      [0 => ['hello' => "World\n"], 1 => []],
      $this->parse('#[@hello("World\n")]')
    );
  }

  #[Test]
  public function dq_string_value_with_at_sign() {
    $this->assertEquals(
      [0 => ['hello' => '@World'], 1 => []],
      $this->parse('#[@hello("@World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_annotation() {
    $this->assertEquals(
      [0 => ['hello' => '@hello(\'World\')'], 1 => []],
      $this->parse('#[@hello("@hello(\'World\')")]')
    );
  }

  #[Test]
  public function int_value() {
    $this->assertEquals(
      [0 => ['answer' => 42], 1 => []],
      $this->parse('#[@answer(42)]')
    );
  }

  #[Test]
  public function double_value() {
    $this->assertEquals(
      [0 => ['version' => 3.5], 1 => []],
      $this->parse('#[@version(3.5)]')
    );
  }

  #[Test]
  public function multi_value_using_short_array() {
    $this->assertEquals(
      [0 => ['xmlmapping' => ['hw_server', 'server']], 1 => []],
      $this->parse("#[@xmlmapping(['hw_server', 'server'])]")
    );
  }

  #[Test]
  public function short_array_value() {
    $this->assertEquals(
      [0 => ['versions' => [3.4, 3.5]], 1 => []],
      $this->parse('#[@versions([3.4, 3.5])]')
    );
  }

  #[Test]
  public function short_array_value_with_nested_arrays() {
    $this->assertEquals(
      [0 => ['versions' => [[3], [4]]], 1 => []],
      $this->parse('#[@versions([[3], [4]])]')
    );
  }

  #[Test]
  public function short_array_value_with_strings_containing_braces() {
    $this->assertEquals(
      [0 => ['versions' => ['(3..4]']], 1 => []],
      $this->parse('#[@versions(["(3..4]"])]')
    );
  }

  #[Test]
  public function bool_true_value() {
    $this->assertEquals(
      [0 => ['supported' => true], 1 => []],
      $this->parse('#[@supported(true)]')
    );
  }

  #[Test]
  public function bool_false_value() {
    $this->assertEquals(
      [0 => ['supported' => false], 1 => []],
      $this->parse('#[@supported(false)]')
    );
  }

  /** @deprecated */
  #[Test]
  public function key_value_pairs_annotation_value() {
    $this->assertEquals(
      [0 => ['config' => ['key' => 'value', 'times' => 5, 'disabled' => false, 'null' => null, 'list' => [1, 2]]], 1 => []],
      $this->parse("#[@config(key = 'value', times= 5, disabled= false, null = null, list= [1, 2])]")
    );
    \xp::gc();
  }

  #[Test]
  public function short_map_value() {
    $this->assertEquals(
      [0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']], 1 => []],
      $this->parse("#[@colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[Test]
  public function multi_line_annotation() {
    $this->assertEquals(
      [0 => ['interceptors' => ['classes' => [
        'net.xp_framework.unittest.core.FirstInterceptor',
        'net.xp_framework.unittest.core.SecondInterceptor',
      ]]], 1 => []],
      $this->parse("
        #[Interceptors(['classes' => [
          'net.xp_framework.unittest.core.FirstInterceptor',
          'net.xp_framework.unittest.core.SecondInterceptor',
        ]])]
      ")
    );
  }

  #[Test]
  public function simple_XPath_annotation() {
    $this->assertEquals(
      [0 => ['fromXml' => ['xpath' => '/parent/child/@attribute']], 1 => []],
      $this->parse("#[@fromXml(['xpath' => '/parent/child/@attribute'])]")
    );
  }

  #[Test]
  public function complex_XPath_annotation() {
    $this->assertEquals(
      [0 => ['fromXml' => ['xpath' => '/parent[@attr="value"]/child[@attr1="val1" and @attr2="val2"]']], 1 => []],
      $this->parse("#[@fromXml(['xpath' => '/parent[@attr=\"value\"]/child[@attr1=\"val1\" and @attr2=\"val2\"]'])]")
    );
  }

  #[Test]
  public function string_with_equal_signs() {
    $this->assertEquals(
      [0 => ['permission' => 'rn=login, rt=config'], 1 => []],
      $this->parse("#[@permission('rn=login, rt=config')]")
    );
  }

  #[Test]
  public function string_assigned_without_whitespace() {
    $this->assertEquals(
      [0 => ['arg' => ['name' => 'verbose', 'short' => 'v']], 1 => []],
      $this->parse("#[@arg(['name' => 'verbose', 'short' => 'v'])]")
    );
  }

  #[Test]
  public function multiple_values_with_strings_and_equal_signs() {
    $this->assertEquals(
      [0 => ['permission' => ['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']]], 1 => []],
      $this->parse("#[@permission(['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']])]")
    );
  }

  #[Test]
  public function unittest_annotation() {
    $this->assertEquals(
      [0 => ['test' => NULL, 'ignore' => NULL, 'limit' => ['time' => 0.1, 'memory' => 100]], 1 => []],
      $this->parse("#[@test, @ignore, @limit(['time' => 0.1, 'memory' => 100])]")
    );
  }

  #[Test]
  public function overloaded_annotation() {
    $this->assertEquals(
      [0 => ['overloaded' => ['signatures' => [['string'], ['string', 'string']]]], 1 => []],
      $this->parse('#[@overloaded(["signatures" => [["string"], ["string", "string"]]])]')
    );
  }

  #[Test]
  public function overloaded_annotation_spanning_multiple_lines() {
    $this->assertEquals(
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
    $this->assertEquals(
      [
        0 => ['webmethod' => ['verb' => 'GET', 'path' => '/greet/{name}']],
        1 => ['$name' => ['path' => null], '$greeting' => ['param' => null]]
      ],
      $this->parse('#[@webmethod(["verb" => "GET", "path" => "/greet/{name}"]), @$name: path, @$greeting: param]')
    );
  }

  #[Test]
  public function map_value_with_short_syntax() {
    $this->assertEquals(
      [0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']], 1 => []],
      $this->parse("#[@colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[Test]
  public function short_array_syntax_as_value() {
    $this->assertEquals(
      [0 => ['permissions' => ['rn=login, rt=config', 'rn=admin, rt=config']], 1 => []],
      $this->parse("#[@permissions(['rn=login, rt=config', 'rn=admin, rt=config'])]")
    );
  }

  #[Test]
  public function short_array_syntax_as_key() {
    $this->assertEquals(
      [0 => ['permissions' => ['names' => ['rn=login, rt=config', 'rn=admin, rt=config']]], 1 => []],
      $this->parse("#[@permissions(['names' => ['rn=login, rt=config', 'rn=admin, rt=config']])]")
    );
  }

  #[Test]
  public function nested_short_array_syntax() {
    $this->assertEquals(
      [0 => ['values' => [[1, 1], [2, 2], [3, 3]]], 1 => []],
      $this->parse("#[@values([[1, 1], [2, 2], [3, 3]])]")
    );
  }

  #[Test]
  public function nested_short_array_syntax_as_key() {
    $this->assertEquals(
      [0 => ['test' => ['values' => [[1, 1], [2, 2], [3, 3]]]], 1 => []],
      $this->parse("#[@test(['values' => [[1, 1], [2, 2], [3, 3]]])]")
    );
  }

  #[Test]
  public function negative_and_positive_floats_inside_array() {
    $this->assertEquals(
      [0 => ['values' => [0.0, -1.5, +1.5]], 1 => []],
      $this->parse("#[@values([0.0, -1.5, +1.5])]")
    );
  }

  #[Test]
  public function class_instance_value() {
    $this->assertEquals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[@value(new Name("hello"))]')
    );
  }

  #[Test]
  public function imported_class_instance_value() {
    $this->assertEquals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[@value(new Name("hello"))]', ['Name' => 'net.xp_framework.unittest.annotations.Name'])
    );
  }

  #[Test]
  public function fully_qualified_class_instance_value() {
    $this->assertEquals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[@value(new \net\xp_framework\unittest\annotations\Name("hello"))]')
    );
  }

  #[Test]
  public function fully_qualified_not_loaded_class() {
    $this->parse('#[@value(new \net\xp_framework\unittest\annotations\NotLoaded())]');
  }


  #[Test]
  public function class_constant_via_self() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(self::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_parent() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(parent::PARENTS_CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_classname() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[@value(\net\xp_framework\unittest\annotations\AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_imported_classname() {
    $this->assertEquals(
      [0 => ['value' => 'namespaced'], 1 => []],
      $this->parse('#[@value(Namespaced::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_self_in_map() {
    $this->assertEquals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[@map(["key" => self::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_classname_in_map() {
    $this->assertEquals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[@map(["key" => AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname_in_map() {
    $this->assertEquals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[@map(["key" => \net\xp_framework\unittest\annotations\AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_public_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'exposed'], 1 => []],
      $this->parse('#[@value(self::$exposed)]')
    );
  }

  #[Test]
  public function parent_public_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'exposed'], 1 => []],
      $this->parse('#[@value(parent::$parentsExposed)]')
    );
  }

  #[Test]
  public function class_protected_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'hidden'], 1 => []],
      $this->parse('#[@value(self::$hidden)]')
    );
  }

  #[Test]
  public function parent_protected_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'hidden'], 1 => []],
      $this->parse('#[@value(parent::$parentsHidden)]')
    );
  }

  #[Test]
  public function class_private_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'internal'], 1 => []],
      $this->parse('#[@value(self::$internal)]')
    );
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Cannot access private static field .+AbstractAnnotationParsingTest::\$parentsInternal/'])]
  public function parent_private_static_member() {
    $this->parse('#[@value(parent::$parentsInternal)]');
  }

  #[Test]
  public function closure() {
    $annotation= $this->parse('#[@value(function() { return true; })]');
    $this->assertInstanceOf('Closure', $annotation[0]['value']);
  }

  #[Test]
  public function closures() {
    $annotation= $this->parse('#[@values([
      function() { return true; },
      function() { return false; }
    ])]');
    $this->assertInstanceOf('Closure[]', $annotation[0]['values']);
  }

  #[Test]
  public function short_closure() {
    $annotation= $this->parse('#[@value(fn() => true)]');
    $this->assertInstanceOf('Closure', $annotation[0]['value']);
  }

  #[Test, Values(['#[Value(fn() => new Name("Test"))]', '#[Value(function() { return new Name("Test"); })]',])]
  public function imports_in_closures($closure) {
    $annotation= $this->parse($closure, ['Name' => 'net.xp_framework.unittest.annotations.Name']);
    $this->assertInstanceOf(Name::class, $annotation[0]['value']());
  }
}