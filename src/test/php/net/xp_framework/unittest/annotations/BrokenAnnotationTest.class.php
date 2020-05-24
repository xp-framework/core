<?php namespace net\xp_framework\unittest\annotations;

use lang\ClassFormatException;
use lang\XPClass;
use lang\reflect\ClassParser;
use unittest\TestCase;

/**
 * Tests the XP Framework's annotations
 *
 * @see   rfc://0185
 * @see   https://github.com/xp-framework/xp-framework/pull/328
 * @see   https://github.com/xp-framework/xp-framework/issues/313
 */
class BrokenAnnotationTest extends TestCase {

  /**
   * Helper
   *
   * @param   string input
   * @return  [:var]
   */
  protected function parse($input) {
    return (new ClassParser())->parseAnnotations($input, nameof($this));
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Unterminated annotation/'])]
  public function no_ending_bracket() {
    XPClass::forName('net.xp_framework.unittest.annotations.NoEndingBracket')->getAnnotations();
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error/'])]
  public function unterminated_single_quoted_string_literal() {
    $this->parse("#[@attribute('value)]");
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error/'])]
  public function unterminated_double_quoted_string_literal() {
    $this->parse('#[@attribute("value)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Expecting "@"/'])]
  public function missing_annotation_after_comma_and_value() {
    $this->parse('#[@ignore("Test"), ]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Expecting "@"/'])]
  public function missing_annotation_after_comma() {
    $this->parse('#[@ignore, ]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Expecting "@"/'])]
  public function missing_annotation_after_second_comma() {
    $this->parse('#[@ignore, @test, ]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unterminated string/'])]
  public function unterminated_dq_string() {
    $this->parse('#[@ignore("Test)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unterminated string/'])]
  public function unterminated_sq_string() {
    $this->parse("#[@ignore('Test)]");
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Unterminated array/'])]
  public function unterminated_short_array() {
    $this->parse('#[@ignore([1');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Unterminated array/'])]
  public function unterminated_short_array_key() {
    $this->parse('#[@ignore(["name" => [1');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Malformed array/'])]
  public function malformed_short_array() {
    $this->parse('#[@ignore([1 ,, 2])]');
  }

  /** @deprecated */
  #[@test]
  public function missing_ending_bracket_in_key_value_pairs() {
    try {
      $this->parse("#[@attribute(key= 'value']");
      $this->fail('No exception raised', null, ClassFormatException::class);
    } catch (ClassFormatException $expected) {
      \xp::gc();
      $this->assertTrue((bool)preg_match('/Unterminated annotation map key/', $expected->getMessage()), $expected->getMessage());
    }
  }

  /** @deprecated */
  #[@test]
  public function malformed_short_array_inside_key_value_pairs() {
    try {
      $this->parse('#[@ignore(name= [1 ,, 2])]');
      \xp::gc();
      $this->fail('No exception raised', null, ClassFormatException::class);
    } catch (ClassFormatException $expected) {
      \xp::gc();
      $this->assertTrue((bool)preg_match('/Malformed array/', $expected->getMessage()), $expected->getMessage());
    }
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Malformed array/'])]
  public function malformed_short_array_no_commas() {
    $this->parse('#[@ignore([1 2])]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Expecting either "\(", "," or "\]"/'])]
  public function annotation_not_separated_by_commas() {
    $this->parse("#[@test @throws('rdbms.SQLConnectException')]");
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Expecting either "\(", "," or "\]"/'])]
  public function too_many_closing_braces() {
    $this->parse("#[@throws('rdbms.SQLConnectException'))]");
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Undefined constant "editor"/'])]
  public function undefined_constant() {
    $this->parse('#[@$editorId: param(editor)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/No such constant "EDITOR" in class/'])]
  public function undefined_class_constant() {
    $this->parse('#[@$editorId: param(self::EDITOR)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Class ".+" could not be found/'])]
  public function undefined_class_in_new() {
    $this->parse('#[@$editorId: param(new NonExistantClass())]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Class ".+" could not be found/'])]
  public function undefined_class_in_constant() {
    $this->parse('#[@$editorId: param(NonExistantClass::CONSTANT)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/No such field "EDITOR" in class/'])]
  public function undefined_class_member() {
    $this->parse('#[@$editorId: param(self::$EDITOR)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Cannot access protected static field .+AnnotationParsingTest::\$hidden/'])]
  public function class_protected_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$hidden)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Cannot access private static field .+AnnotationParsingTest::\$internal/'])]
  public function class_private_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$internal)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/In `.+`: (Syntax error|Unmatched)/i'])]
  public function function_without_braces() {
    $this->parse('#[@value(function)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/In `.+`: (Syntax error|Unmatched)/i'])]
  public function function_without_body() {
    $this->parse('#[@value(function())]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/In `.+`: (Syntax error|Unclosed)/i'])]
  public function function_without_closing_curly() {
    $this->parse('#[@value(function() {)]');
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function multi_value() {
    $this->parse("#[@xmlmapping('hw_server', 'server')]");
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function multi_value_without_whitespace() {
    $this->parse("#[@xmlmapping('hw_server','server')]");
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function multi_value_with_variable_types_backwards_compatibility() {
    $this->parse("#[@xmlmapping('hw_server', TRUE)]");
  }

  #[@test, @expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function parsingContinuesAfterMultiValue() {
    $this->parse("#[@xmlmapping('hw_server', 'server'), @restricted]");
  }
}
