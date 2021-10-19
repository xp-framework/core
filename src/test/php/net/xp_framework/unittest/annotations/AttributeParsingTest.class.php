<?php namespace net\xp_framework\unittest\annotations;

use lang\ClassFormatException;
use lang\reflect\ClassParser;
use net\xp_framework\unittest\annotations\fixture\Namespaced;
use unittest\{Expect, Interceptors, Test, Value, Values};

/**
 * Tests the XP Framework's annotation parsing implementation
 *
 * @see     https://github.com/xp-framework/rfc/issues/336
 * @see     https://gist.github.com/1240769
 */
class AttributeParsingTest extends AbstractAnnotationParsingTest {
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
    return (new ClassParser())->parseAnnotations($input, nameof($this), array_merge($imports, [
      'Namespaced' => 'net.xp_framework.unittest.annotations.fixture.Namespaced'
    ]));
  }

  #[Test, Values(['#[Hello]', '#[Hello()]'])]
  public function no_value($declaration) {
    $this->assertEquals(
      [0 => ['hello' => null], 1 => []],
      $this->parse($declaration)
    );
  }


  #[Test]
  public function sq_string_value() {
    $this->assertEquals(
      [0 => ['hello' => 'World'], 1 => []],
      $this->parse("#[Hello('World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_equals_sign() {
    $this->assertEquals(
      [0 => ['hello' => 'World=Welt'], 1 => []],
      $this->parse("#[Hello('World=Welt')]")
    );
  }

  #[Test]
  public function sq_string_value_with_at_sign() {
    $this->assertEquals(
      [0 => ['hello' => '@World'], 1 => []],
      $this->parse("#[Hello('@World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_annotation() {
    $this->assertEquals(
      [0 => ['hello' => '@hello("World")'], 1 => []],
      $this->parse("#[Hello('@hello(\"World\")')]")
    );
  }

  #[Test]
  public function sq_string_value_with_double_quotes() {
    $this->assertEquals(
      [0 => ['hello' => 'said "he"'], 1 => []],
      $this->parse("#[Hello('said \"he\"')]")
    );
  }

  #[Test]
  public function sq_string_value_with_escaped_single_quotes() {
    $this->assertEquals(
      [0 => ['hello' => "said 'he'"], 1 => []],
      $this->parse("#[Hello('said \'he\'')]")
    );
  }

  #[Test]
  public function dq_string_value() {
    $this->assertEquals(
      [0 => ['hello' => 'World'], 1 => []],
      $this->parse('#[Hello("World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_single_quote() {
    $this->assertEquals(
      [0 => ['hello' => 'Beck\'s'], 1 => []],
      $this->parse('#[Hello("Beck\'s")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escaped_double_quotes() {
    $this->assertEquals(
      [0 => ['hello' => 'said "he"'], 1 => []],
      $this->parse('#[Hello("said \"he\"")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escape_sequence() {
    $this->assertEquals(
      [0 => ['hello' => "World\n"], 1 => []],
      $this->parse('#[Hello("World\n")]')
    );
  }

  #[Test]
  public function dq_string_value_with_at_sign() {
    $this->assertEquals(
      [0 => ['hello' => '@World'], 1 => []],
      $this->parse('#[Hello("@World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_annotation() {
    $this->assertEquals(
      [0 => ['hello' => '@hello(\'World\')'], 1 => []],
      $this->parse('#[Hello("@hello(\'World\')")]')
    );
  }

  #[Test]
  public function int_value() {
    $this->assertEquals(
      [0 => ['answer' => 42], 1 => []],
      $this->parse('#[Answer(42)]')
    );
  }

  #[Test]
  public function double_value() {
    $this->assertEquals(
      [0 => ['version' => 3.5], 1 => []],
      $this->parse('#[Version(3.5)]')
    );
  }

  #[Test]
  public function multi_value_using_array() {
    $this->assertEquals(
      [0 => ['xmlMapping' => ['hw_server', 'server']], 1 => []],
      $this->parse("#[XmlMapping(['hw_server', 'server'])]")
    );
  }

  #[Test]
  public function array_value() {
    $this->assertEquals(
      [0 => ['versions' => [3.4, 3.5]], 1 => []],
      $this->parse('#[Versions([3.4, 3.5])]')
    );
  }

  #[Test]
  public function array_value_with_nested_arrays() {
    $this->assertEquals(
      [0 => ['versions' => [[3], [4]]], 1 => []],
      $this->parse('#[Versions([[3], [4]])]')
    );
  }

  #[Test]
  public function array_value_with_strings_containing_braces() {
    $this->assertEquals(
      [0 => ['versions' => ['(3..4]']], 1 => []],
      $this->parse('#[Versions(["(3..4]"])]')
    );
  }

  #[Test]
  public function bool_true_value() {
    $this->assertEquals(
      [0 => ['supported' => true], 1 => []],
      $this->parse('#[Supported(true)]')
    );
  }

  #[Test]
  public function bool_false_value() {
    $this->assertEquals(
      [0 => ['supported' => false], 1 => []],
      $this->parse('#[Supported(false)]')
    );
  }

  #[Test]
  public function named_arguments() {
    $this->assertEquals(
      [0 => ['config' => ['key' => 'value', 'times' => 5, 'disabled' => false, 'null' => null, 'list' => [1, 2]]], 1 => []],
      $this->parse("#[Config(key: 'value', times: 5, disabled: false, null: null, list: [1, 2])]")
    );
    \xp::gc();
  }

  #[Test]
  public function map_value() {
    $this->assertEquals(
      [0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']], 1 => []],
      $this->parse("#[Colors(['green' => '$10.50', 'red' => '$9.99'])]")
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
        #[Interceptors(classes: [
          'net.xp_framework.unittest.core.FirstInterceptor',
          'net.xp_framework.unittest.core.SecondInterceptor',
        ])]
      ")
    );
  }

  #[Test]
  public function simple_XPath_annotation() {
    $this->assertEquals(
      [0 => ['fromXml' => ['xpath' => '/parent/child/@attribute']], 1 => []],
      $this->parse("#[FromXml(['xpath' => '/parent/child/@attribute'])]")
    );
  }

  #[Test]
  public function complex_XPath_annotation() {
    $this->assertEquals(
      [0 => ['fromXml' => ['xpath' => '/parent[@attr="value"]/child[@attr1="val1" and @attr2="val2"]']], 1 => []],
      $this->parse("#[FromXml(['xpath' => '/parent[@attr=\"value\"]/child[@attr1=\"val1\" and @attr2=\"val2\"]'])]")
    );
  }

  #[Test]
  public function string_with_equal_signs() {
    $this->assertEquals(
      [0 => ['permission' => 'rn=login, rt=config'], 1 => []],
      $this->parse("#[Permission('rn=login, rt=config')]")
    );
  }

  #[Test]
  public function string_assigned_without_whitespace() {
    $this->assertEquals(
      [0 => ['arg' => ['name' => 'verbose', 'short' => 'v']], 1 => []],
      $this->parse("#[Arg(['name' => 'verbose', 'short' => 'v'])]")
    );
  }

  #[Test]
  public function multiple_values_with_strings_and_equal_signs() {
    $this->assertEquals(
      [0 => ['permission' => ['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']]], 1 => []],
      $this->parse("#[Permission(['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']])]")
    );
  }

  #[Test]
  public function unittest_annotation() {
    $this->assertEquals(
      [0 => ['test' => NULL, 'ignore' => NULL, 'limit' => ['time' => 0.1, 'memory' => 100]], 1 => []],
      $this->parse("#[Test, Ignore, Limit(['time' => 0.1, 'memory' => 100])]")
    );
  }

  #[Test]
  public function overloaded_annotation() {
    $this->assertEquals(
      [0 => ['overloaded' => ['signatures' => [['string'], ['string', 'string']]]], 1 => []],
      $this->parse('#[Overloaded(["signatures" => [["string"], ["string", "string"]]])]')
    );
  }

  #[Test]
  public function overloaded_annotation_spanning_multiple_lines() {
    $this->assertEquals(
      [0 => ['overloaded' => ['signatures' => [['string'], ['string', 'string']]]], 1 => []],
      $this->parse(
        "#[Overloaded(['signatures' => [\n".
        "  ['string'],\n".
        "  ['string', 'string']\n".
        "]])]"
      )
    );
  }

  #[Test]
  public function map_value_with_short_syntax() {
    $this->assertEquals(
      [0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']], 1 => []],
      $this->parse("#[Colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[Test]
  public function array_syntax_as_value() {
    $this->assertEquals(
      [0 => ['permissions' => ['rn=login, rt=config', 'rn=admin, rt=config']], 1 => []],
      $this->parse("#[Permissions(['rn=login, rt=config', 'rn=admin, rt=config'])]")
    );
  }

  #[Test]
  public function array_syntax_as_key() {
    $this->assertEquals(
      [0 => ['permissions' => ['names' => ['rn=login, rt=config', 'rn=admin, rt=config']]], 1 => []],
      $this->parse("#[Permissions(['names' => ['rn=login, rt=config', 'rn=admin, rt=config']])]")
    );
  }

  #[Test]
  public function nested_array_syntax() {
    $this->assertEquals(
      [0 => ['values' => [[1, 1], [2, 2], [3, 3]]], 1 => []],
      $this->parse("#[Values([[1, 1], [2, 2], [3, 3]])]")
    );
  }

  #[Test]
  public function nested_array_syntax_as_key() {
    $this->assertEquals(
      [0 => ['test' => ['values' => [[1, 1], [2, 2], [3, 3]]]], 1 => []],
      $this->parse("#[Test(['values' => [[1, 1], [2, 2], [3, 3]]])]")
    );
  }

  #[Test]
  public function negative_and_positive_floats_inside_array() {
    $this->assertEquals(
      [0 => ['values' => [0.0, -1.5, +1.5]], 1 => []],
      $this->parse("#[Values([0.0, -1.5, +1.5])]")
    );
  }

  #[Test]
  public function class_instance_value() {
    $this->assertEquals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[Value(new Name("hello"))]')
    );
  }

  #[Test]
  public function imported_class_instance_value() {
    $this->assertEquals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[Value(new Name("hello"))]', ['Name' => 'net.xp_framework.unittest.annotations.Name'])
    );
  }

  #[Test]
  public function fully_qualified_class_instance_value() {
    $this->assertEquals(
      [0 => ['value' => new Name('hello')], 1 => []],
      $this->parse('#[Value(new \net\xp_framework\unittest\annotations\Name("hello"))]')
    );
  }

  #[Test]
  public function fully_qualified_not_loaded_class() {
    $this->parse('#[Value(new \net\xp_framework\unittest\annotations\NotLoaded())]');
  }


  #[Test]
  public function class_constant_via_self() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[Value(self::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_parent() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[Value(parent::PARENTS_CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_classname() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[Value(AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname() {
    $this->assertEquals(
      [0 => ['value' => 'constant'], 1 => []],
      $this->parse('#[Value(\net\xp_framework\unittest\annotations\AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_imported_classname() {
    $this->assertEquals(
      [0 => ['value' => 'namespaced'], 1 => []],
      $this->parse('#[Value(Namespaced::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_self_in_map() {
    $this->assertEquals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[Map(["key" => self::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_classname_in_map() {
    $this->assertEquals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[Map(["key" => AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname_in_map() {
    $this->assertEquals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => []],
      $this->parse('#[Map(["key" => \net\xp_framework\unittest\annotations\AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_public_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'exposed'], 1 => []],
      $this->parse('#[Value(eval: "self::\$exposed")]')
    );
  }

  #[Test]
  public function parent_public_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'exposed'], 1 => []],
      $this->parse('#[Value(eval: "parent::\$parentsExposed")]')
    );
  }

  #[Test]
  public function class_protected_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'hidden'], 1 => []],
      $this->parse('#[Value(eval: "self::\$hidden")]')
    );
  }

  #[Test]
  public function parent_protected_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'hidden'], 1 => []],
      $this->parse('#[Value(eval: "parent::\$parentsHidden")]')
    );
  }

  #[Test]
  public function class_private_static_member() {
    $this->assertEquals(
      [0 => ['value' => 'internal'], 1 => []],
      $this->parse('#[Value(eval: "self::\$internal")]')
    );
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Cannot access private static field .+AbstractAnnotationParsingTest::\$parentsInternal/'])]
  public function parent_private_static_member() {
    $this->parse('#[Value(eval: "parent::\$parentsInternal")]');
  }

  #[Test]
  public function closurel() {
    $annotation= $this->parse('#[Value(eval: "function() { return true; }")]');
    $this->assertInstanceOf('Closure', $annotation[0]['value']);
  }

  #[Test]
  public function closuresl() {
    $annotation= $this->parse('#[Values(eval: "[
      function() { return true; },
      function() { return false; }
    ]")]');
    $this->assertInstanceOf('Closure[]', $annotation[0]['values']);
  }

  #[Test]
  public function short_closure_via_eval() {
    $annotation= $this->parse('#[Value(eval: "fn() => true")]');
    $this->assertInstanceOf('Closure', $annotation[0]['value']);
  }

  #[Test, Values(['#[Value(eval: "fn() => new Name(\"Test\")")]', '#[Value(eval: "function() { return new Name(\"Test\"); }")]',])]
  public function imports_in_closures($closure) {
    $annotation= $this->parse($closure, ['Name' => 'net.xp_framework.unittest.annotations.Name']);
    $this->assertInstanceOf(Name::class, $annotation[0]['value']());
  }
}