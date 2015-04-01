<?php namespace net\xp_framework\unittest\annotations;

use net\xp_framework\unittest\annotations\fixture\Namespaced;
use lang\reflect\ClassParser;

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
    return (new ClassParser())->parseAnnotations($input, $this->getClassName(), array_merge($imports, array(
      'Namespaced' => 'net.xp_framework.unittest.annotations.fixture.Namespaced'
    )));
  }

  #[@test]
  public function no_value() {
    $this->assertEquals(
      array(0 => array('hello' => NULL), 1 => []),
      $this->parse("#[@hello]")
    );
  }

  #[@test]
  public function sq_string_value() {
    $this->assertEquals(
      array(0 => array('hello' => 'World'), 1 => []),
      $this->parse("#[@hello('World')]")
    );
  }

  #[@test]
  public function sq_string_value_with_equals_sign() {
    $this->assertEquals(
      array(0 => array('hello' => 'World=Welt'), 1 => []),
      $this->parse("#[@hello('World=Welt')]")
    );
  }

  #[@test]
  public function sq_string_value_with_at_sign() {
    $this->assertEquals(
      array(0 => array('hello' => '@World'), 1 => []),
      $this->parse("#[@hello('@World')]")
    );
  }

  #[@test]
  public function sq_string_value_with_annotation() {
    $this->assertEquals(
      array(0 => array('hello' => '@hello("World")'), 1 => []),
      $this->parse("#[@hello('@hello(\"World\")')]")
    );
  }

  #[@test]
  public function sq_string_value_with_double_quotes() {
    $this->assertEquals(
      array(0 => array('hello' => 'said "he"'), 1 => []),
      $this->parse("#[@hello('said \"he\"')]")
    );
  }

  #[@test]
  public function sq_string_value_with_escaped_single_quotes() {
    $this->assertEquals(
      array(0 => array('hello' => "said 'he'"), 1 => []),
      $this->parse("#[@hello('said \'he\'')]")
    );
  }

  #[@test]
  public function dq_string_value() {
    $this->assertEquals(
      array(0 => array('hello' => 'World'), 1 => []),
      $this->parse('#[@hello("World")]')
    );
  }

  #[@test]
  public function dq_string_value_with_single_quote() {
    $this->assertEquals(
      array(0 => array('hello' => 'Beck\'s'), 1 => []),
      $this->parse('#[@hello("Beck\'s")]')
    );
  }

  #[@test]
  public function dq_string_value_with_escaped_double_quotes() {
    $this->assertEquals(
      array(0 => array('hello' => 'said "he"'), 1 => []),
      $this->parse('#[@hello("said \"he\"")]')
    );
  }

  #[@test]
  public function dq_string_value_with_escape_sequence() {
    $this->assertEquals(
      array(0 => array('hello' => "World\n"), 1 => []),
      $this->parse('#[@hello("World\n")]')
    );
  }

  #[@test]
  public function dq_string_value_with_at_sign() {
    $this->assertEquals(
      array(0 => array('hello' => '@World'), 1 => []),
      $this->parse('#[@hello("@World")]')
    );
  }

  #[@test]
  public function dq_string_value_with_annotation() {
    $this->assertEquals(
      array(0 => array('hello' => '@hello(\'World\')'), 1 => []),
      $this->parse('#[@hello("@hello(\'World\')")]')
    );
  }

  #[@test]
  public function int_value() {
    $this->assertEquals(
      array(0 => array('answer' => 42), 1 => []),
      $this->parse('#[@answer(42)]')
    );
  }

  #[@test]
  public function double_value() {
    $this->assertEquals(
      array(0 => array('version' => 3.5), 1 => []),
      $this->parse('#[@version(3.5)]')
    );
  }

  #[@test]
  public function multi_value_using_short_array() {
    $this->assertEquals(
      array(0 => array('xmlmapping' => array('hw_server', 'server')), 1 => []),
      $this->parse("#[@xmlmapping(['hw_server', 'server'])]")
    );
  }

  #[@test]
  public function array_value() {
    $this->assertEquals(
      array(0 => array('versions' => array(3.4, 3.5)), 1 => []),
      $this->parse('#[@versions(array(3.4, 3.5))]')
    );
  }

  #[@test]
  public function array_value_with_nested_array() {
    $this->assertEquals(
      array(0 => array('versions' => array(array(3))), 1 => []),
      $this->parse('#[@versions(array(array(3)))]')
    );
  }

  #[@test]
  public function array_value_with_nested_arrays() {
    $this->assertEquals(
      array(0 => array('versions' => array(array(3), array(4))), 1 => []),
      $this->parse('#[@versions(array(array(3), array(4)))]')
    );
  }

  #[@test]
  public function array_value_with_strings_containing_braces() {
    $this->assertEquals(
      array(0 => array('versions' => array('(3..4]')), 1 => []),
      $this->parse('#[@versions(array("(3..4]"))]')
    );
  }

  #[@test]
  public function bool_true_value() {
    $this->assertEquals(
      array(0 => array('supported' => true), 1 => []),
      $this->parse('#[@supported(true)]')
    );
  }

  #[@test]
  public function bool_false_value() {
    $this->assertEquals(
      array(0 => array('supported' => false), 1 => []),
      $this->parse('#[@supported(false)]')
    );
  }

  #[@test]
  public function key_value_pairs_annotation_value() {
    $this->assertEquals(
      array(0 => array('config' => array('key' => 'value', 'times' => 5, 'disabled' => false, 'null' => null, 'list' => array(1, 2))), 1 => []), 
      $this->parse("#[@config(key = 'value', times= 5, disabled= false, null = null, list= array(1, 2))]")
    );
  }

  #[@test]
  public function map_value() {
    $this->assertEquals(
      array(0 => array('colors' => array('green' => '$10.50', 'red' => '$9.99')), 1 => []),
      $this->parse("#[@colors(array('green' => '$10.50', 'red' => '$9.99'))]")
    );
  }

  #[@test]
  public function multi_line_annotation() {
    $this->assertEquals(
      array(0 => array('interceptors' => array('classes' => array(
        'net.xp_framework.unittest.core.FirstInterceptor',
        'net.xp_framework.unittest.core.SecondInterceptor',
      ))), 1 => []),
      $this->parse("
        #[@interceptors(classes= array(
          'net.xp_framework.unittest.core.FirstInterceptor',
          'net.xp_framework.unittest.core.SecondInterceptor'
        ))]
      ")
    );
  }

  #[@test]
  public function simple_XPath_annotation() {
    $this->assertEquals(
      array(0 => array('fromXml' => array('xpath' => '/parent/child/@attribute')), 1 => []),
      $this->parse("#[@fromXml(xpath= '/parent/child/@attribute')]")
    );
  }

  #[@test]
  public function complex_XPath_annotation() {
    $this->assertEquals(
      array(0 => array('fromXml' => array('xpath' => '/parent[@attr="value"]/child[@attr1="val1" and @attr2="val2"]')), 1 => []),
      $this->parse("#[@fromXml(xpath= '/parent[@attr=\"value\"]/child[@attr1=\"val1\" and @attr2=\"val2\"]')]")
    );
  }

  #[@test]
  public function string_with_equal_signs() {
    $this->assertEquals(
      array(0 => array('permission' => 'rn=login, rt=config'), 1 => []),
      $this->parse("#[@permission('rn=login, rt=config')]")
    );
  }

  #[@test]
  public function string_assigned_without_whitespace() {
    $this->assertEquals(
      array(0 => array('arg' => array('name' => 'verbose', 'short' => 'v')), 1 => []),
      $this->parse("#[@arg(name= 'verbose', short='v')]")
    );
  }

  #[@test]
  public function multiple_values_with_strings_and_equal_signs() {
    $this->assertEquals(
      array(0 => array('permission' => array('names' => array('rn=login, rt=config1', 'rn=login, rt=config2'))), 1 => []),
      $this->parse("#[@permission(names= array('rn=login, rt=config1', 'rn=login, rt=config2'))]")
    );
  }

  #[@test]
  public function unittest_annotation() {
    $this->assertEquals(
      array(0 => array('test' => NULL, 'ignore' => NULL, 'limit' => array('time' => 0.1, 'memory' => 100)), 1 => []),
      $this->parse("#[@test, @ignore, @limit(time = 0.1, memory = 100)]")
    );
  }

  #[@test]
  public function overloaded_annotation() {
    $this->assertEquals(
      array(0 => array('overloaded' => array('signatures' => array(array('string'), array('string', 'string')))), 1 => []),
      $this->parse('#[@overloaded(signatures= array(array("string"), array("string", "string")))]')
    );
  }

  #[@test]
  public function overloaded_annotation_spanning_multiple_lines() {
    $this->assertEquals(
      array(0 => array('overloaded' => array('signatures' => array(array('string'), array('string', 'string')))), 1 => []),
      $this->parse(
        "#[@overloaded(signatures= array(\n".
        "  array('string'),\n".
        "  array('string', 'string')\n".
        "))]"
      )
    );
  }

  #[@test]
  public function webmethod_with_parameter_annotations() {
    $this->assertEquals(
      array(
        0 => array('webmethod' => array('verb' => 'GET', 'path' => '/greet/{name}')),
        1 => array('$name' => array('path' => NULL), '$greeting' => array('param' => NULL))
      ),
      $this->parse('#[@webmethod(verb= "GET", path= "/greet/{name}"), @$name: path, @$greeting: param]')
    );
  }

  #[@test]
  public function map_value_with_short_syntax() {
    $this->assertEquals(
      array(0 => array('colors' => array('green' => '$10.50', 'red' => '$9.99')), 1 => []),
      $this->parse("#[@colors(['green' => '$10.50', 'red' => '$9.99'])]")
    );
  }

  #[@test]
  public function short_array_syntax_as_value() {
    $this->assertEquals(
      array(0 => array('permissions' => array('rn=login, rt=config', 'rn=admin, rt=config')), 1 => []),
      $this->parse("#[@permissions(['rn=login, rt=config', 'rn=admin, rt=config'])]")
    );
  }

  #[@test]
  public function short_array_syntax_as_key() {
    $this->assertEquals(
      array(0 => array('permissions' => array('names' => array('rn=login, rt=config', 'rn=admin, rt=config'))), 1 => []),
      $this->parse("#[@permissions(names = ['rn=login, rt=config', 'rn=admin, rt=config'])]")
    );
  }

  #[@test]
  public function nested_short_array_syntax() {
    $this->assertEquals(
      array(0 => array('values' => array(array(1, 1), array(2, 2), array(3, 3))), 1 => []),
      $this->parse("#[@values([[1, 1], [2, 2], [3, 3]])]")
    );
  }

  #[@test]
  public function nested_short_array_syntax_as_key() {
    $this->assertEquals(
      array(0 => array('test' => array('values' => array(array(1, 1), array(2, 2), array(3, 3)))), 1 => []),
      $this->parse("#[@test(values = [[1, 1], [2, 2], [3, 3]])]")
    );
  }

  #[@test]
  public function combined_long_and_short_array_syntaxes() {
    $this->assertEquals(
      array(0 => array('values' => array(array(1, 1), array(2, 2), array(3, 3))), 1 => []),
      $this->parse("#[@values(array([1, 1], [2, 2], [3, 3]))]")
    );
  }

  #[@test]
  public function combined_short_and_long_array_syntaxes() {
    $this->assertEquals(
      array(0 => array('values' => array(array(1, 1), array(2, 2), array(3, 3))), 1 => []),
      $this->parse("#[@values([array(1, 1), array(2, 2), array(3, 3)])]")
    );
  }

  #[@test]
  public function negative_and_positive_floats_inside_array() {
    $this->assertEquals(
      array(0 => array('values' => array(0.0, -1.5, +1.5)), 1 => []),
      $this->parse("#[@values(array(0.0, -1.5, +1.5))]")
    );
  }

  #[@test]
  public function class_instance_value() {
    $this->assertEquals(
      array(0 => array('value' => new \lang\types\String('hello')), 1 => []),
      $this->parse('#[@value(new String("hello"))]', array('String' => 'lang.types.String'))
    );
  }

  #[@test]
  public function ns_class_instance_value() {
    $this->assertEquals(
      array(0 => array('value' => new \lang\types\String('hello')), 1 => []),
      $this->parse('#[@value(new \lang\types\String("hello"))]')
    );
  }

  #[@test]
  public function class_constant_via_self() {
    $this->assertEquals(
      array(0 => array('value' => 'constant'), 1 => []),
      $this->parse('#[@value(self::CONSTANT)]')
    );
  }

  #[@test]
  public function class_constant_via_parent() {
    $this->assertEquals(
      array(0 => array('value' => 'constant'), 1 => []),
      $this->parse('#[@value(parent::PARENTS_CONSTANT)]')
    );
  }

  #[@test]
  public function class_constant_via_classname() {
    $this->assertEquals(
      array(0 => array('value' => 'constant'), 1 => []),
      $this->parse('#[@value(AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[@test]
  public function class_constant_via_ns_classname() {
    $this->assertEquals(
      array(0 => array('value' => 'constant'), 1 => []),
      $this->parse('#[@value(\net\xp_framework\unittest\annotations\AnnotationParsingTest::CONSTANT)]')
    );
  }

  #[@test]
  public function class_constant_via_imported_classname() {
    $this->assertEquals(
      array(0 => array('value' => 'namespaced'), 1 => []),
      $this->parse('#[@value(Namespaced::CONSTANT)]')
    );
  }

  #[@test]
  public function class_constant_via_self_in_map() {
    $this->assertEquals(
      array(0 => array('map' => array('key' => 'constant', 'value' => 'val')), 1 => []),
      $this->parse('#[@map(key = self::CONSTANT, value = "val")]')
    );
  }

  #[@test]
  public function class_constant_via_classname_in_map() {
    $this->assertEquals(
      array(0 => array('map' => array('key' => 'constant', 'value' => 'val')), 1 => []),
      $this->parse('#[@map(key = AnnotationParsingTest::CONSTANT, value = "val")]')
    );
  }

  #[@test]
  public function class_constant_via_ns_classname_in_map() {
    $this->assertEquals(
      array(0 => array('map' => array('key' => 'constant', 'value' => 'val')), 1 => []),
      $this->parse('#[@map(key = \net\xp_framework\unittest\annotations\AnnotationParsingTest::CONSTANT, value = "val")]')
    );
  }

  #[@test]
  public function class_public_static_member() {
    $this->assertEquals(
      array(0 => array('value' => 'exposed'), 1 => []),
      $this->parse('#[@value(self::$exposed)]')
    );
  }

  #[@test]
  public function parent_public_static_member() {
    $this->assertEquals(
      array(0 => array('value' => 'exposed'), 1 => []),
      $this->parse('#[@value(parent::$parentsExposed)]')
    );
  }

  #[@test]
  public function class_protected_static_member() {
    $this->assertEquals(
      array(0 => array('value' => 'hidden'), 1 => []),
      $this->parse('#[@value(self::$hidden)]')
    );
  }

  #[@test]
  public function parent_protected_static_member() {
    $this->assertEquals(
      array(0 => array('value' => 'hidden'), 1 => []),
      $this->parse('#[@value(parent::$parentsHidden)]')
    );
  }

  #[@test]
  public function class_private_static_member() {
    $this->assertEquals(
      array(0 => array('value' => 'internal'), 1 => []),
      $this->parse('#[@value(self::$internal)]')
    );
  }

  #[@test, @expect(class= 'lang.ClassFormatException', withMessage= '/Cannot access private static field .+AbstractAnnotationParsingTest::\$parentsInternal/')]
  public function parent_private_static_member() {
    $this->parse('#[@value(parent::$parentsInternal)]');
  }

  #[@test]
  public function closure() {
    $annotation= $this->parse('#[@value(function() { return true; })]');
    $this->assertInstanceOf('Closure', $annotation[0]['value']);
  }

  #[@test]
  public function closures() {
    $annotation= $this->parse('#[@values([
      function() { return true; },
      function() { return false; }
    ])]');
    $this->assertInstanceOf('Closure[]', $annotation[0]['values']);
  }
}
