<?php namespace util\unittest;

use unittest\Action;
use unittest\actions\ExtensionAvailable;
use util\Secret;

#[Action(eval: 'new ExtensionAvailable("sodium")')]
class SodiumSecretTest extends SecretTest {

  /** @return int */
  protected function backing() { return Secret::BACKING_SODIUM; }
}