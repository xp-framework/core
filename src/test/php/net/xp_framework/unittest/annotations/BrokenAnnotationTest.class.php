<?php namespace net\xp_framework\unittest\annotations;

use lang\reflect\ClassParser;
use lang\{ClassFormatException, XPClass};
use unittest\actions\RuntimeVersion;
use unittest\{Assert, Action, Expect, Test};

class BrokenAnnotationTest {

  /**
   * Helper
   *
   * @param  string $input
   * @return [:var]
   */
  protected function parse($input) {
    try {
      return (new ClassParser())->parseAnnotations($input, nameof($this));
    } finally {
      \xp::gc(); // Strip deprecation warning
    }
  }

  #[Test, Action(eval: 'new RuntimeVersion("<8.0")'), Expect(['class' => ClassFormatException::class, 'withMessage' => '/Unterminated annotation/'])]
  public function no_ending_bracket() {
    try {
      XPClass::forName('net.xp_framework.unittest.annotations.NoEndingBracket')->getAnnotations();
    } finally {
      \xp::gc(); // Strip deprecation warning
    }
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error/'])]
  public function unterminated_single_quoted_string_literal() {
    $this->parse("#[@attribute('value)]");
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error/'])]
  public function unterminated_double_quoted_string_literal() {
    $this->parse('#[@attribute("value)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unterminated string/'])]
  public function unterminated_dq_string() {
    $this->parse('#[@ignore("Test)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unterminated string/'])]
  public function unterminated_sq_string() {
    $this->parse("#[@ignore('Test)]");
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Unterminated array/'])]
  public function unterminated_short_array() {
    $this->parse('#[@ignore([1');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Unterminated array/'])]
  public function unterminated_short_array_key() {
    $this->parse('#[@ignore(["name" => [1');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Malformed array/'])]
  public function malformed_short_array() {
    $this->parse('#[@ignore([1 ,, 2])]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Malformed array/'])]
  public function malformed_short_array_no_commas() {
    $this->parse('#[@ignore([1 2])]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Expecting either "\(", "," or "\]"/'])]
  public function annotation_not_separated_by_commas() {
    $this->parse("#[@test @throws('rdbms.SQLConnectException')]");
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Expecting either "\(", "," or "\]"/'])]
  public function too_many_closing_braces() {
    $this->parse("#[@throws('rdbms.SQLConnectException'))]");
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Undefined constant "editor"/'])]
  public function undefined_constant() {
    $this->parse('#[@$editorId: param(editor)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/No such constant "EDITOR" in class/'])]
  public function undefined_class_constant() {
    $this->parse('#[@$editorId: param(self::EDITOR)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Class ".+" could not be found/'])]
  public function undefined_class_in_new() {
    $this->parse('#[@$editorId: param(new NonExistantClass())]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Class ".+" could not be found/'])]
  public function undefined_class_in_constant() {
    $this->parse('#[@$editorId: param(NonExistantClass::CONSTANT)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/No such field "EDITOR" in class/'])]
  public function undefined_class_member() {
    $this->parse('#[@$editorId: param(self::$EDITOR)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Cannot access protected static field .+AnnotationParsingTest::\$hidden/'])]
  public function class_protected_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$hidden)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Cannot access private static field .+AnnotationParsingTest::\$internal/'])]
  public function class_private_static_member() {
    $this->parse('#[@value(AnnotationParsingTest::$internal)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/In `.+`: (Syntax error|Unmatched)/i'])]
  public function function_without_braces() {
    $this->parse('#[@value(function)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/In `.+`: (Syntax error|Unmatched)/i'])]
  public function function_without_body() {
    $this->parse('#[@value(function())]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/In `.+`: (Syntax error|Unclosed)/i'])]
  public function function_without_closing_curly() {
    $this->parse('#[@value(function() {)]');
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function multi_value() {
    $this->parse("#[@xmlmapping('hw_server', 'server')]");
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function multi_value_without_whitespace() {
    $this->parse("#[@xmlmapping('hw_server','server')]");
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function multi_value_with_variable_types_backwards_compatibility() {
    $this->parse("#[@xmlmapping('hw_server', TRUE)]");
  }

  #[Test, Expect(['class' => ClassFormatException::class, 'withMessage' => '/Parse error: Unexpected ","/'])]
  public function parsingContinuesAfterMultiValue() {
    $this->parse("#[@xmlmapping('hw_server', 'server'), @restricted]");
  }
}