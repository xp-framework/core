<?php namespace net\xp_framework\unittest\core;

use lang\{
  ClassCastException,
  Error,
  IllegalArgumentException,
  IndexOutOfBoundsException,
  NullPointerException,
  Value,
  XPException
};
use unittest\actions\RuntimeVersion;
use net\xp_framework\unittest\IgnoredOnHHVM;

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
      throw new XPException('');
    } catch (XPException $e) {
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

  #[@test, @expect(NullPointerException::class)]
  public function undefined_variable_yields_npe() {
    $a++;
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function undefined_array_offset_yields_ioobe() {
    $a= [];
    $a[0];
  }

  #[@test, @expect(IndexOutOfBoundsException::class)]
  public function undefined_map_key_yields_ioobe() {
    $a= [];
    $a['test'];
  }

  #[@test, @expect(IndexOutOfBoundsException::class), @action(new IgnoredOnHHVM())]
  public function undefined_string_offset_yields_ioobe() {
    $a= '';
    $a{0};
  }

  #[@test, @expect(Error::class)]
  public function call_to_member_on_non_object_yields_error() {
    $a= null;
    $a->method();
  }

  #[@test, @expect(Error::class)]
  public function argument_mismatch_yield_type_exception() {
    $f= function(Value $arg) { };
    $f('Primitive');
  }

  #[@test, @expect(IllegalArgumentException::class), @action([
  #  new RuntimeVersion('<7.1.0-dev')
  #])]
  public function missing_argument_mismatch_yield_iae() {
    $f= function($arg) { };
    $f();
  }

  #[@test, @expect(Error::class), @action([
  #  new IgnoredOnHHVM(),
  #  new RuntimeVersion('>=7.1.0-dev')
  #])]
  public function missing_argument_mismatch_yield_error() {
    $f= function($arg) { };
    $f();
  }

  #[@test, @expect(ClassCastException::class)]
  public function cannot_convert_object_to_string_yields_cce() {
    $object= new class() { };
    $object.'String';
  }

  #[@test, @expect(ClassCastException::class)]
  public function cannot_convert_array_to_string_yields_cce() {
    $array= [];
    $array.'String';
  }

  #[@test, @expect(ClassCastException::class)]
  public function __toString_not_returning_a_string_yields_cce() {
    $object= new class() {
      public function __toString() { return null; }
    };
    (string)$object;
  }
}