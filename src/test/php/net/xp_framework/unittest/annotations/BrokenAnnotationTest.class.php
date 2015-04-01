<?php namespace net\xp_framework\unittest\annotations;

use lang\XPClass;
use lang\reflect\ClassParser;
use lang\ClassFormatException;

/**
 * Tests the XP Framework's annotations
 *
 * @see   rfc://0185
 * @see   https://github.com/xp-framework/xp-framework/pull/328
 * @see   https://github.com/xp-framework/xp-framework/issues/313
 */
class BrokenAnnotationTest extends \unittest\TestCase {

  /**
   * Helper
   *
   * @param   string input
   * @return  [:var]
   */
  protected function parse($input) {
    return (new ClassParser())->parseAnnotations($input, $this->getClassName());
  }

  #[@test, @expect(ClassFormatException::class)]
  public function no_ending_bracket() {
    XPClass::forName('net.xp_framework.unittest.annotations.NoEndingBracket')->getAnnotations();
  }

  #[@test, @expect(ClassFormatException::class)]
  public function missing_ending_bracket_in_key_value_pairs() {
    $this->parse("#[@attribute(key= 'value']");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function unterminated_single_quoted_string_literal() {
    $this->parse("#[@attribute(key= 'value)]");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function unterminated_double_quoted_string_literal() {
    $this->parse('#[@attribute(key= "value)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function missing_annotation_after_comma_and_value() {
    $this->parse('#[@ignore("Test"), ]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function missing_annotation_after_comma() {
    $this->parse('#[@ignore, ]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function missing_annotation_after_second_comma() {
    $this->parse('#[@ignore, @test, ]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function unterminated_dq_string() {
    $this->parse('#[@ignore("Test)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function unterminated_sq_string() {
    $this->parse("#[@ignore('Test)]");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function unterminated_array() {
    $this->parse('#[@ignore(array(1]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function unterminated_array_key() {
    $this->parse('#[@ignore(name = array(1]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function malformed_array() {
    $this->parse('#[@ignore(array(1 ,, 2))]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function malformed_array_inside_key_value_pairs() {
    $this->parse('#[@ignore(name= array(1 ,, 2))]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function malformed_array_no_commas() {
    $this->parse('#[@ignore(array(1 2))]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function annotation_not_separated_by_commas() {
    $this->parse("#[@test @throws('rdbms.SQLConnectException')]");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function too_many_closing_braces() {
    $this->parse("#[@throws('rdbms.SQLConnectException'))]");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function undefined_constant() {
    $this->parse('#[@$editorId: param(editor)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function undefined_class_constant() {
    $this->parse('#[@$editorId: param(self::EDITOR)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function undefined_class_in_new() {
    $this->parse('#[@$editorId: param(new NonExistantClass())]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function undefined_class_in_constant() {
    $this->parse('#[@$editorId: param(NonExistantClass::CONSTANT)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function undefined_class_member() {
    $this->parse('#[@$editorId: param(self::$EDITOR)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function class_protected_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$hidden)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function class_private_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$internal)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function function_without_braces() {
    $this->parse('#[@value(function)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function function_without_body() {
    $this->parse('#[@value(function())]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function function_without_closing_curly() {
    $this->parse('#[@value(function() {)]');
  }

  #[@test, @expect(ClassFormatException::class)]
  public function multi_value() {
    $this->parse("#[@xmlmapping('hw_server', 'server')]");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function multi_value_without_whitespace() {
    $this->parse("#[@xmlmapping('hw_server','server')]");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function multi_value_with_variable_types_backwards_compatibility() {
    $this->parse("#[@xmlmapping('hw_server', TRUE)]");
  }

  #[@test, @expect(ClassFormatException::class)]
  public function parsingContinuesAfterMultiValue() {
    $this->parse("#[@xmlmapping('hw_server', 'server'), @restricted]");
  }
}
