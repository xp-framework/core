<?php namespace net\xp_framework\unittest\security\checksum;

use unittest\TestCase;
use security\checksum\MessageDigest;
use lang\IllegalStateException;

abstract class AbstractDigestTest extends TestCase {
  protected $fixture;
  
  /**
   * Creates a new message digest object
   *
   * @return security.checksum.MessageDigest
   */
  protected abstract function newDigest();

  /**
   * Returns a checksum for a given input string
   *
   * @param  string $input
   * @return security.checksum.Checksum
   */
  protected abstract function checksumOf($input);

  /** @return void */
  public function setUp() {
    $this->fixture= $this->newDigest();
  }
  
  #[@test]
  public function singleUpdate() {
    $this->fixture->update('Hello');
    $this->assertEquals($this->checksumOf('Hello'), $this->fixture->digest());
  }

  #[@test]
  public function multipleUpdates() {
    $this->fixture->update('Hello');
    $this->fixture->update('World');
    $this->assertEquals($this->checksumOf('HelloWorld'), $this->fixture->digest());
  }

  #[@test]
  public function noUpdate() {
    $this->assertEquals($this->checksumOf(''), $this->fixture->digest());
  }

  #[@test]
  public function digestOnly() {
    $this->assertEquals($this->checksumOf('Hello'), $this->fixture->digest('Hello'));
  }

  #[@test, @expect(IllegalStateException::class)]
  public function callingUpdateAfterFinalization() {
    $this->fixture->update('...');
    $this->fixture->digest();
    $this->fixture->update('...');
  }

  #[@test, @expect(IllegalStateException::class)]
  public function callingDigestAfterFinalization() {
    $this->fixture->update('...');
    $this->fixture->digest();
    $this->fixture->digest();
  }
}