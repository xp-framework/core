<?php namespace net\xp_framework\unittest\security\checksum;

use security\checksum\MessageDigest;
use security\NoSuchAlgorithmException;
use lang\IllegalArgumentException;

class MessageDigestTest extends \unittest\TestCase {

  #[@test]
  public function supported_algorithms() {
    $this->assertInstanceOf('string[]', MessageDigest::supportedAlgorithms());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function register_this_as_implementation() {
    MessageDigest::register('irrelevant', typeof($this));
  }

  #[@test, @expect(NoSuchAlgorithmException::class)]
  public function unsupported_algorithm() {
    MessageDigest::newInstance('unsupported');
  }
}
