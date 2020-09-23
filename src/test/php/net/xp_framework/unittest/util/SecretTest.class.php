<?php namespace net\xp_framework\unittest\util;

use lang\{IllegalArgumentException, IllegalStateException, Throwable, XPException};
use unittest\{Expect, Test, Values, TestCase};
use util\Secret;

/**
 * Baseclass for test cases for security.Secret
 */
abstract class SecretTest extends TestCase {

  #[Test]
  public function create() {
    new Secret('payload');
  }

  #[Test]
  public function create_from_function_return_value() {
    $f= function() { return 'payload'; };
    new Secret($f());
  }

  #[Test, Expect(IllegalStateException::class)]
  public function not_serializable() {
    serialize(new Secret('payload'));
  }

  #[Test]
  public function var_export_not_revealing_payload() {
    $export= var_export(new Secret('payload'), true);
    $this->assertFalse(strpos($export, 'payload'));
  }

  #[Test]
  public function var_dump_not_revealing_payload() {
    ob_start();
    var_dump(new Secret('payload'));

    $output= ob_get_contents();
    ob_end_clean();

    $this->assertFalse(strpos($output, 'payload'));
  }

  #[Test]
  public function toString_not_revealing_payload() {
    $output= (new Secret('payload'))->toString();
    $this->assertFalse(strpos($output, 'payload'));
  }

  #[Test]
  public function string_cast_not_revealing_payload() {
    $output= (string)new Secret('payload');
    $this->assertFalse(strpos($output, 'payload'));
  }

  #[Test]
  public function array_cast_not_revealing_payload() {
    $output= var_export((array)new Secret('payload'), 1);
    $this->assertFalse(strpos($output, 'payload'));
  }

  #[Test]
  public function getPayload_reveals_original_data() {
    $secure= new Secret('payload');
    $this->assertEquals('payload', $secure->reveal());
  }

  #[Test]
  public function big_data() {
    $data= str_repeat('*', 1024000);
    $secure= new Secret($data);
    $this->assertEquals($data, $secure->reveal());
  }

  #[Test]
  public function creation_never_throws_exception() {
    $called= false;
    Secret::setBacking(function($value) use (&$called) {
      $called= true;
      throw new XPException('Something went wrong - intentionally.');
    }, function($value) { return null; });

    new Secret('foo');
    $this->assertTrue($called);
  }

  #[Test, Expect(['class' => IllegalStateException::class, 'withMessage' => '/An error occurred during storing the encrypted secret./'])]
  public function decryption_throws_exception_if_creation_has_failed() {
    $called= false;
    Secret::setBacking(function($value) {
      throw new XPException('Something went wrong - intentionally.');
    }, function($value) { return null; });

    // Creation may never throw exception
    try {
      $s= new Secret('foo');
    } catch (\Throwable $t) {
      $this->fail('Exception thrown where no exception may be thrown', $t, null);
    }

    // Buf if creation failed, an exception must be raised here:
    $s->reveal();
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function useBacking_with_invalid_backing_throws_exception() {
    Secret::useBacking(77);
  }

  #[Test]
  public function equals_original_data() {
    $this->assertTrue((new Secret('payload'))->equals('payload'));
  }

  #[Test]
  public function equals_itself() {
    $fixture= new Secret('payload');
    $this->assertTrue($fixture->equals($fixture));
  }

  #[Test, Values([null, 'payloa', 'PAYLOAD', "payload\0", "\0payload"])]
  public function does_not_match($value) {
    $this->assertFalse((new Secret('payload'))->equals($value));
  }
}