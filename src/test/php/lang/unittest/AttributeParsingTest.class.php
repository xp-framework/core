<?php namespace lang\unittest;

use lang\ClassFormatException;
use lang\reflect\ClassParser;
use lang\unittest\fixture\Namespaced;
use test\{Assert, Expect, Interceptors, Test, Value, Values};

class AttributeParsingTest extends AbstractAnnotationParsingTest {
  const CONSTANT= 'constant';
  public static $exposed= 'exposed';
  protected static $hidden= 'hidden';
  private static $internal= 'internal';

  /**
   * Helper
   *
   * @param  string $input
   * @param  [:var] $imports
   * @return [:var]
   */
  protected function parse($input, $imports= []) {
    return (new ClassParser())->parseAnnotations($input, nameof($this), array_merge($imports, [
      'Namespaced' => 'lang.unittest.fixture.Namespaced'
    ]));
  }

  #[Test, Values(['#[Hello]', '#[Hello()]'])]
  public function no_value($declaration) {
    Assert::equals(
      [0 => ['hello' => null], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse($declaration)
    );
  }


  #[Test]
  public function sq_string_value() {
    Assert::equals(
      [0 => ['hello' => 'World'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse("#[Hello('World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_equals_sign() {
    Assert::equals(
      [0 => ['hello' => 'World=Welt'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse("#[Hello('World=Welt')]")
    );
  }

  #[Test]
  public function sq_string_value_with_at_sign() {
    Assert::equals(
      [0 => ['hello' => '@World'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse("#[Hello('@World')]")
    );
  }

  #[Test]
  public function sq_string_value_with_annotation() {
    Assert::equals(
      [0 => ['hello' => '@hello("World")'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse("#[Hello('@hello(\"World\")')]")
    );
  }

  #[Test]
  public function sq_string_value_with_double_quotes() {
    Assert::equals(
      [0 => ['hello' => 'said "he"'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse("#[Hello('said \"he\"')]")
    );
  }

  #[Test]
  public function sq_string_value_with_escaped_single_quotes() {
    Assert::equals(
      [0 => ['hello' => "said 'he'"], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse("#[Hello('said \'he\'')]")
    );
  }

  #[Test]
  public function dq_string_value() {
    Assert::equals(
      [0 => ['hello' => 'World'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse('#[Hello("World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_single_quote() {
    Assert::equals(
      [0 => ['hello' => 'Beck\'s'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse('#[Hello("Beck\'s")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escaped_double_quotes() {
    Assert::equals(
      [0 => ['hello' => 'said "he"'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse('#[Hello("said \"he\"")]')
    );
  }

  #[Test]
  public function dq_string_value_with_escape_sequence() {
    Assert::equals(
      [0 => ['hello' => "World\n"], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse('#[Hello("World\n")]')
    );
  }

  #[Test]
  public function dq_string_value_with_at_sign() {
    Assert::equals(
      [0 => ['hello' => '@World'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse('#[Hello("@World")]')
    );
  }

  #[Test]
  public function dq_string_value_with_annotation() {
    Assert::equals(
      [0 => ['hello' => '@hello(\'World\')'], 1 => ['hello' => 'lang.unittest.Hello']],
      $this->parse('#[Hello("@hello(\'World\')")]')
    );
  }

  #[Test]
  public function int_value() {
    Assert::equals(
      [0 => ['answer' => 42], 1 => ['answer' => 'lang.unittest.Answer']],
      $this->parse('#[Answer(42)]')
    );
  }

  #[Test]
  public function double_value() {
    Assert::equals(
      [0 => ['version' => 3.5], 1 => ['version' => 'lang.unittest.Version']],
      $this->parse('#[Version(3.5)]')
    );
  }

  #[Test]
  public function multi_value_using_array() {
    Assert::equals(
      [0 => ['xmlMapping' => ['hw_server', 'server']], 1 => ['xmlMapping' => 'lang.unittest.XmlMapping']],
      $this->parse("#[XmlMapping(['hw_server', 'server'])]")
    );
  }

  #[Test]
  public function array_value() {
    Assert::equals(
      [0 => ['versions' => [3.4, 3.5]], 1 => ['versions' => 'lang.unittest.Versions']],
      $this->parse('#[Versions([3.4, 3.5])]')
    );
  }

  #[Test]
  public function array_value_with_nested_arrays() {
    Assert::equals(
      [0 => ['versions' => [[3], [4]]], 1 => ['versions' => 'lang.unittest.Versions']],
      $this->parse('#[Versions([[3], [4]])]')
    );
  }

  #[Test]
  public function array_value_with_strings_containing_braces() {
    Assert::equals(
      [0 => ['versions' => ['(3..4]']], 1 => ['versions' => 'lang.unittest.Versions']],
      $this->parse('#[Versions(["(3..4]"])]')
    );
  }

  #[Test]
  public function bool_true_value() {
    Assert::equals(
      [0 => ['supported' => true], 1 => ['supported' => 'lang.unittest.Supported']],
      $this->parse('#[Supported(true)]')
    );
  }

  #[Test]
  public function bool_false_value() {
    Assert::equals(
      [0 => ['supported' => false], 1 => ['supported' => 'lang.unittest.Supported']],
      $this->parse('#[Supported(false)]')
    );
  }

  #[Test]
  public function named_arguments() {
    Assert::equals(
      [
        0 => ['config' => ['key' => 'value', 'times' => 5, 'disabled' => false, 'null' => null, 'list' => [1, 2]]],
        1 => ['config' => 'lang.unittest.Config']
      ],
      $this->parse("#[Config(key: 'value', times: 5, disabled: false, null: null, list: [1, 2])]")
    );
    \xp::gc();
  }

  #[Test]
  public function map_value() {
    Assert::equals(
      [0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']], 1 => ['colors' => 'lang.unittest.Colors']],
      $this->parse("#[Colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[Test]
  public function multi_line_annotation() {
    Assert::equals(
      [
        0 => ['interceptors' => ['classes' => [
          'lang.unittest.FirstInterceptor',
          'lang.unittest.SecondInterceptor',
        ]]],
        1 => ['interceptors' => 'lang.unittest.Interceptors']
      ],
      $this->parse("
        #[Interceptors(classes: [
          'lang.unittest.FirstInterceptor',
          'lang.unittest.SecondInterceptor',
        ])]
      ")
    );
  }

  #[Test]
  public function simple_XPath_annotation() {
    Assert::equals(
      [
        0 => ['fromXml' => ['xpath' => '/parent/child/@attribute']],
        1 => ['fromXml' => 'lang.unittest.FromXml']
      ],
      $this->parse("#[FromXml(['xpath' => '/parent/child/@attribute'])]")
    );
  }

  #[Test]
  public function complex_XPath_annotation() {
    Assert::equals(
      [
        0 => ['fromXml' => ['xpath' => '/parent[@attr="value"]/child[@attr1="val1" and @attr2="val2"]']],
        1 => ['fromXml' => 'lang.unittest.FromXml']
      ],
      $this->parse("#[FromXml(['xpath' => '/parent[@attr=\"value\"]/child[@attr1=\"val1\" and @attr2=\"val2\"]'])]")
    );
  }

  #[Test]
  public function string_with_equal_signs() {
    Assert::equals(
      [0 => ['permission' => 'rn=login, rt=config'], 1 => ['permission' => 'lang.unittest.Permission']],
      $this->parse("#[Permission('rn=login, rt=config')]")
    );
  }

  #[Test]
  public function string_assigned_without_whitespace() {
    Assert::equals(
      [0 => ['arg' => ['name' => 'verbose', 'short' => 'v']], 1 => ['arg' => 'lang.unittest.Arg']],
      $this->parse("#[Arg(['name' => 'verbose', 'short' => 'v'])]")
    );
  }

  #[Test]
  public function multiple_values_with_strings_and_equal_signs() {
    Assert::equals(
      [
        0 => ['permission' => ['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']]],
        1 => ['permission' => 'lang.unittest.Permission']
      ],
      $this->parse("#[Permission(['names' => ['rn=login, rt=config1', 'rn=login, rt=config2']])]")
    );
  }

  #[Test]
  public function unittest_annotation() {
    Assert::equals(
      [
        0 => ['test' => NULL, 'ignore' => NULL, 'limit' => ['time' => 0.1, 'memory' => 100]],
        1 => ['test' => 'lang.unittest.Test', 'ignore' => 'lang.unittest.Ignore', 'limit' => 'lang.unittest.Limit']
      ],
      $this->parse("#[Test, Ignore, Limit(['time' => 0.1, 'memory' => 100])]")
    );
  }

  #[Test]
  public function overloaded_annotation() {
    Assert::equals(
      [
        0 => ['overloaded' => ['signatures' => [['string'], ['string', 'string']]]],
        1 => ['overloaded' => 'lang.unittest.Overloaded']
      ],
      $this->parse('#[Overloaded(["signatures" => [["string"], ["string", "string"]]])]')
    );
  }

  #[Test]
  public function overloaded_annotation_spanning_multiple_lines() {
    Assert::equals(
      [
        0 => ['overloaded' => ['signatures' => [['string'], ['string', 'string']]]],
        1 => ['overloaded' => 'lang.unittest.Overloaded']
      ],
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
    Assert::equals(
      [
        0 => ['colors' => ['green' => '$10.50', 'red' => '$9.99']],
        1 => ['colors' => 'lang.unittest.Colors']
      ],
      $this->parse("#[Colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[Test]
  public function array_syntax_as_value() {
    Assert::equals(
      [
        0 => ['permissions' => ['rn=login, rt=config', 'rn=admin, rt=config']],
        1 => ['permissions' => 'lang.unittest.Permissions']
      ],
      $this->parse("#[Permissions(['rn=login, rt=config', 'rn=admin, rt=config'])]")
    );
  }

  #[Test]
  public function array_syntax_as_key() {
    Assert::equals(
      [
        0 => ['permissions' => ['names' => ['rn=login, rt=config', 'rn=admin, rt=config']]],
        1 => ['permissions' => 'lang.unittest.Permissions']
      ],
      $this->parse("#[Permissions(['names' => ['rn=login, rt=config', 'rn=admin, rt=config']])]")
    );
  }

  #[Test]
  public function nested_array_syntax() {
    Assert::equals(
      [0 => ['values' => [[1, 1], [2, 2], [3, 3]]], 1 => ['values' => 'lang.unittest.Values']],
      $this->parse("#[Values([[1, 1], [2, 2], [3, 3]])]")
    );
  }

  #[Test]
  public function nested_array_syntax_as_key() {
    Assert::equals(
      [0 => ['test' => ['values' => [[1, 1], [2, 2], [3, 3]]]], 1 => ['test' => 'lang.unittest.Test']],
      $this->parse("#[Test(['values' => [[1, 1], [2, 2], [3, 3]]])]")
    );
  }

  #[Test]
  public function negative_and_positive_floats_inside_array() {
    Assert::equals(
      [0 => ['values' => [0.0, -1.5, +1.5]], 1 => ['values' => 'lang.unittest.Values']],
      $this->parse("#[Values([0.0, -1.5, +1.5])]")
    );
  }

  #[Test]
  public function class_instance_value() {
    Assert::equals(
      [0 => ['value' => new Name('hello')], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(new Name("hello"))]')
    );
  }

  #[Test]
  public function imported_class_instance_value() {
    Assert::equals(
      [0 => ['value' => new Name('hello')], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(new Name("hello"))]', ['Name' => 'lang.unittest.Name'])
    );
  }

  #[Test]
  public function fully_qualified_class_instance_value() {
    Assert::equals(
      [0 => ['value' => new Name('hello')], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(new \lang\unittest\Name("hello"))]')
    );
  }

  #[Test]
  public function fully_qualified_not_loaded_class() {
    $this->parse('#[Value(new \lang\unittest\NotLoaded())]');
  }


  #[Test]
  public function class_constant_via_self() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(self::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_parent() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(parent::PARENTS_CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_classname() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname() {
    Assert::equals(
      [0 => ['value' => 'constant'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(\lang\unittest\AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_imported_classname() {
    Assert::equals(
      [0 => ['value' => 'namespaced'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(Namespaced::CONSTANT)]')
    );
  }

  #[Test]
  public function class_constant_via_self_in_map() {
    Assert::equals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => ['map' => 'lang.unittest.Map']],
      $this->parse('#[Map(["key" => self::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_classname_in_map() {
    Assert::equals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => ['map' => 'lang.unittest.Map']],
      $this->parse('#[Map(["key" => AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_constant_via_ns_classname_in_map() {
    Assert::equals(
      [0 => ['map' => ['key' => 'constant', 'value' => 'val']], 1 => ['map' => 'lang.unittest.Map']],
      $this->parse('#[Map(["key" => \lang\unittest\AnnotationParsingTest::CONSTANT, "value" => "val"])]')
    );
  }

  #[Test]
  public function class_public_static_member() {
    Assert::equals(
      [0 => ['value' => 'exposed'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(eval: "self::\$exposed")]')
    );
  }

  #[Test]
  public function parent_public_static_member() {
    Assert::equals(
      [0 => ['value' => 'exposed'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(eval: "parent::\$parentsExposed")]')
    );
  }

  #[Test]
  public function class_protected_static_member() {
    Assert::equals(
      [0 => ['value' => 'hidden'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(eval: "self::\$hidden")]')
    );
  }

  #[Test]
  public function parent_protected_static_member() {
    Assert::equals(
      [0 => ['value' => 'hidden'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(eval: "parent::\$parentsHidden")]')
    );
  }

  #[Test]
  public function class_private_static_member() {
    Assert::equals(
      [0 => ['value' => 'internal'], 1 => ['value' => 'lang.unittest.Value']],
      $this->parse('#[Value(eval: "self::\$internal")]')
    );
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Cannot access private static field .+AbstractAnnotationParsingTest::\$parentsInternal/')]
  public function parent_private_static_member() {
    $this->parse('#[Value(eval: "parent::\$parentsInternal")]');
  }

  #[Test]
  public function closurel() {
    $annotation= $this->parse('#[Value(eval: "function() { return true; }")]');
    Assert::instance('Closure', $annotation[0]['value']);
  }

  #[Test]
  public function closuresl() {
    $annotation= $this->parse('#[Values(eval: "[
      function() { return true; },
      function() { return false; }
    ]")]');
    Assert::instance('Closure[]', $annotation[0]['values']);
  }

  #[Test]
  public function short_closure_via_eval() {
    $annotation= $this->parse('#[Value(eval: "fn() => true")]');
    Assert::instance('Closure', $annotation[0]['value']);
  }

  #[Test, Values(['#[Value(eval: "fn() => new Name(\"Test\")")]', '#[Value(eval: "function() { return new Name(\"Test\"); }")]',])]
  public function imports_in_closures($closure) {
    $annotation= $this->parse($closure, ['Name' => 'lang.unittest.Name']);
    Assert::instance(Name::class, $annotation[0]['value']());
  }
}