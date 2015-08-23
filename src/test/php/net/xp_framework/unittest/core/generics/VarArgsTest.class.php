<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalArgumentException;

/**
 * TestCase for generic construction behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.ListOf
 */
class VarArgsTest extends \unittest\TestCase {

  #[@test]
  public function withArguments() {
    $this->assertEquals(
      ['Hello', 'World'],
      create('new net.xp_framework.unittest.core.generics.ListOf<string>', 'Hello', 'World')->elements()
    );
  }

  #[@test]
  public function withoutArguments() {
    $this->assertEquals(
      [],
      create('new net.xp_framework.unittest.core.generics.ListOf<string>')->elements()
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function withIncorrectArguments() {
    create('new net.xp_framework.unittest.core.generics.ListOf<string>', 'Hello', 1);
  }

  #[@test]
  public function withAllOf() {
    $this->assertEquals(
      ['Hello', 'World'],
      create('new net.xp_framework.unittest.core.generics.ListOf<string>')->withAll('Hello', 'World')->elements()
    );
  }
}
