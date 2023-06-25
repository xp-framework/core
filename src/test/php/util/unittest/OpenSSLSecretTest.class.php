<?php namespace util\unittest;

use test\verify\Runtime;
use util\Secret;

#[Runtime(extensions: ['sodium'])]
class OpenSSLSecretTest extends SecretTest {

  /** @return int */
  protected function backing() { return Secret::BACKING_OPENSSL; }
}