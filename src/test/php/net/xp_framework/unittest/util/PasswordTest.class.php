<?php namespace net\xp_framework\unittest\util;

use util\Password;
use lang\IllegalStateException;
use lang\IllegalArgumentException;
use lang\XPException;
use lang\Throwable;

/**
 * Baseclass for test cases for security.Password
 */
abstract class PasswordTest extends \unittest\TestCase {

  #[@test]
  public function create() {
    new Password('payload');
  }

  #[@test]
  public function create_from_function_return_value() {
    $f= function() { return 'payload'; };
    new Password($f());
  }

  #[@test, @expect(IllegalStateException::class)]
  public function not_serializable() {
    serialize(new Password('payload'));
  }

  #[@test]
  public function var_export_not_revealing_payload() {
    $export= var_export(new Password('payload'), true);
    $this->assertFalse(strpos($export, 'payload'));
  }

  #[@test]
  public function var_dump_not_revealing_payload() {
    ob_start();
    var_dump(new Password('payload'));

    $output= ob_get_contents();
    ob_end_clean();

    $this->assertFalse(strpos($output, 'payload'));
  }

  #[@test]
  public function toString_not_revealing_payload() {
    $output= (new Password('payload'))->toString();
    $this->assertFalse(strpos($output, 'payload'));
  }

  #[@test]
  public function string_cast_not_revealing_payload() {
    $output= (string)new Password('payload');
    $this->assertFalse(strpos($output, 'payload'));
  }

  #[@test]
  public function array_cast_not_revealing_payload() {
    $output= var_export((array)new Password('payload'), 1);
    $this->assertFalse(strpos($output, 'payload'));
  }

  #[@test]
  public function getPayload_reveals_original_data() {
    $secure= new Password('payload');
    $this->assertEquals('payload', $secure->characters());
  }

  #[@test]
  public function big_data() {
    $data= str_repeat('*', 1024000);
    $secure= new Password($data);
    $this->assertEquals($data, $secure->characters());
  }

  #[@test]
  public function creation_never_throws_exception() {
    $called= false;
    Password::setBacking(function($value) use (&$called) {
      $called= true;
      throw new XPException('Something went wrong - intentionally.');
    }, function($value) { return null; });

    new Password('foo');
    $this->assertTrue($called);
  }

  #[@test, @expect(class= IllegalStateException::class, withMessage= '/An error occurred during storing the encrypted password./')]
  public function decryption_throws_exception_if_creation_has_failed() {
    $called= false;
    Password::setBacking(function($value) {
      throw new XPException('Something went wrong - intentionally.');
    }, function($value) { return null; });

    // Creation may never throw exception
    try {
      $s= new Password('foo');
    } catch (\Throwable $t) {
      $this->fail('Exception thrown where no exception may be thrown', $t, null);
    }

    // Buf if creation failed, an exception must be raised here:
    $s->characters();
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function useBacking_with_invalid_backing_throws_exception() {
    Password::useBacking(77);
  }
}
