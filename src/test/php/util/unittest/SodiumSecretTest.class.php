<?php namespace util\unittest;

use test\verify\Runtime;
use util\Secret;

#[Runtime(extensions: ['sodium'])]
class SodiumSecretTest extends SecretTest {

  /** @return int */
  protected function backing() { return Secret::BACKING_SODIUM; }
}