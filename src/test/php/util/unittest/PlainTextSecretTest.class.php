<?php namespace util\unittest;

use util\Secret;

class PlainTextSecretTest extends SecretTest {

  /** @return int */
  protected function backing() { return Secret::BACKING_PLAINTEXT; }
}