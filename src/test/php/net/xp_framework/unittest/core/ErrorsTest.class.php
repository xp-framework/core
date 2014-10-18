<?php namespace net\xp_framework\unittest\core;

use lang\Object;
use unittest\actions\RuntimeVersion;

/**
 * Test the XP error handling semantics
 */
class ErrorsTest extends \unittest\TestCase {

  /**
   * Setup method. Ensures xp error registry is initially empty and
   * that the error reporting level is set to E_ALL (which is done
   * in lang.base.php).
   */
  public function setUp() {
    $this->assertEquals(E_ALL, error_reporting(), 'Error reporting level not E_ALL');
    \xp::$errors= [];
  }

  /**
   * Teardown method. Clears the xp error registry.
   */
  public function tearDown() {
    \xp::gc();
  }

  #[@test]
  public function errors_get_appended_to_registry() {
    trigger_error('Test error');
    $this->assertEquals(1, sizeof(\xp::$errors));
  }

  #[@test]
  public function errors_appear_in_stacktrace() {
    trigger_error('Test error');
      
    try {
      throw new \lang\XPException('');
    } catch (\lang\XPException $e) {
      $element= $e->getStackTrace()[0];
      $this->assertEquals(
        ['file' => __FILE__, 'message' => 'Test error'],
        ['file' => $element->file, 'message' => $element->message]
      );
    }
  }

  #[@test]
  public function errorAt_given_a_file_without_error() {
    $this->assertFalse((bool)\xp::errorAt(__FILE__));
  }

  #[@test]
  public function errorAt_given_a_file_with_error() {
    trigger_error('Test error');
    $this->assertTrue((bool)\xp::errorAt(__FILE__));
  }

  #[@test]
  public function errorAt_given_a_file_and_line_without_error() {
    $this->assertFalse((bool)\xp::errorAt(__FILE__, __LINE__ - 1));
  }

  #[@test]
  public function errorAt_given_a_file_and_line_with_error() {
    trigger_error('Test error');
    $this->assertTrue((bool)\xp::errorAt(__FILE__, __LINE__ - 1));
  }

  #[@test, @expect('lang.NullPointerException')]
  public function undefined_variable_yields_npe() {
    $a++;
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function undefined_array_offset_yields_npe() {
    $a= [];
    $a[0];
  }

  #[@test, @expect('lang.IndexOutOfBoundsException')]
  public function undefined_string_offset_yields_npe() {
    $a= '';
    $a[0];
  }

  #[@test, @expect('lang.NullPointerException'), @action(new RuntimeVersion('>=7.0'))]
  public function call_to_member_on_non_object_yields_npe() {
    $a= null;
    $a->method();
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function argument_mismatch_yield_iae() {
    $f= function(Object $arg) { };
    $f('Primitive');
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function missing_argument_mismatch_yield_iae() {
    $f= function($arg) { };
    $f();
  }

  #[@test, @expect('lang.ClassCastException')]
  public function cannot_convert_object_to_string_yields_cce() {
    (new Object()).'String';
  }

  #[@test, @expect('lang.ClassCastException')]
  public function cannot_convert_array_to_string_yields_cce() {
    [].'String';
  }
}