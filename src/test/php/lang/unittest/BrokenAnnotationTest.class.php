<?php namespace lang\unittest;

use lang\reflect\ClassParser;
use lang\{ClassFormatException, XPClass};
use test\{Action, Assert, Expect, Test};

class BrokenAnnotationTest {

  /**
   * Helper
   *
   * @param  string $input
   * @return [:var]
   */
  private function parse($input) {
    try {
      return (new ClassParser())->parseAnnotations($input, nameof($this));
    } finally {
      \xp::gc(); // Strip deprecation warning
    }
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Unterminated annotation/')]
  public function no_ending_bracket() {
    $this->parse("#[@attribute\n");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error/')]
  public function unterminated_single_quoted_string_literal() {
    $this->parse("#[@attribute('value)]");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error/')]
  public function unterminated_double_quoted_string_literal() {
    $this->parse('#[@attribute("value)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Unterminated string/')]
  public function unterminated_dq_string() {
    $this->parse('#[@ignore("Test)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Unterminated string/')]
  public function unterminated_sq_string() {
    $this->parse("#[@ignore('Test)]");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Unterminated array/')]
  public function unterminated_short_array() {
    $this->parse('#[@ignore([1');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Unterminated array/')]
  public function unterminated_short_array_key() {
    $this->parse('#[@ignore(["name" => [1');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Malformed array/')]
  public function malformed_short_array() {
    $this->parse('#[@ignore([1 ,, 2])]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Malformed array/')]
  public function malformed_short_array_no_commas() {
    $this->parse('#[@ignore([1 2])]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Expecting either "\(", "," or "\]"/')]
  public function annotation_not_separated_by_commas() {
    $this->parse("#[@test @throws('rdbms.SQLConnectException')]");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Expecting either "\(", "," or "\]"/')]
  public function too_many_closing_braces() {
    $this->parse("#[@throws('rdbms.SQLConnectException'))]");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Undefined constant "editor"/')]
  public function undefined_constant() {
    $this->parse('#[@$editorId: param(editor)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/No such constant "EDITOR" in class/')]
  public function undefined_class_constant() {
    $this->parse('#[@$editorId: param(self::EDITOR)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Class ".+" could not be found/')]
  public function undefined_class_in_new() {
    $this->parse('#[@$editorId: param(new NonExistantClass())]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Class ".+" could not be found/')]
  public function undefined_class_in_constant() {
    $this->parse('#[@$editorId: param(NonExistantClass::CONSTANT)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/No such field "EDITOR" in class/')]
  public function undefined_class_member() {
    $this->parse('#[@$editorId: param(self::$EDITOR)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Cannot access protected static field .+AnnotationParsingTest::\$hidden/')]
  public function class_protected_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$hidden)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Cannot access private static field .+AnnotationParsingTest::\$internal/')]
  public function class_private_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$internal)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/In `.+`: (Syntax error|Unmatched)/i')]
  public function function_without_braces() {
    $this->parse('#[@value(function)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/In `.+`: (Syntax error|Unmatched)/i')]
  public function function_without_body() {
    $this->parse('#[@value(function())]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/In `.+`: (Syntax error|Unclosed)/i')]
  public function function_without_closing_curly() {
    $this->parse('#[@value(function() {)]');
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Unexpected ","/')]
  public function multi_value() {
    $this->parse("#[@xmlmapping('hw_server', 'server')]");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Unexpected ","/')]
  public function multi_value_without_whitespace() {
    $this->parse("#[@xmlmapping('hw_server','server')]");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Unexpected ","/')]
  public function multi_value_with_variable_types_backwards_compatibility() {
    $this->parse("#[@xmlmapping('hw_server', TRUE)]");
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Parse error: Unexpected ","/')]
  public function parsingContinuesAfterMultiValue() {
    $this->parse("#[@xmlmapping('hw_server', 'server'), @restricted]");
  }
}