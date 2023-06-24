<?php namespace net\xp_framework\unittest\util;

use util\Secret;

class PlainTextSecretTest extends SecretTest {

  /** @return int */
  protected function backing() { return Secret::BACKING_PLAINTEXT; }
}