<?php namespace util\unittest;

use unittest\Action;
use unittest\actions\ExtensionAvailable;
use util\Secret;

#[Action(eval: 'new ExtensionAvailable("openssl")')]
class OpenSSLSecretTest extends SecretTest {

  /** @return int */
  protected function backing() { return Secret::BACKING_OPENSSL; }
}