<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalArgumentException;
use unittest\Assert;
use unittest\{Expect, Test};

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class ArrayTest {

  #[Test]
  public function primitiveStringArrayValue() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, string[]>', [
      'this' => [$this->name]
    ]);
    Assert::equals([$this->name], $l->get('this'));
  }

  #[Test]
  public function primitiveStringArrayKey() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string[], unittest.TestCase>');
    $l->put(['this'], $this);
    Assert::equals($this, $l->get(['this']));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function stringToArrayOfStringInvalid() {
    create('new net.xp_framework.unittest.core.generics.Lookup<string, string[]>')
      ->put('greeting', ['Hello', 'World', '!!!', 1])
    ;
  }

  #[Test]
  public function stringToArrayOfStringMultiple() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, string[]>', [
      'colors' => ['red', 'green', 'blue'],
      'names'  => ['PHP', 'Java', 'C#']
    ]);
    Assert::equals(['red', 'green', 'blue'], $l->get('colors'));
    Assert::equals(['PHP', 'Java', 'C#'], $l->get('names'));
  }
 
  #[Test]
  public function arrayOfStringToStringMultiple() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string[], string>');
    $l->put(['red', 'green', 'blue'], 'colors');
    $l->put(['PHP', 'Java', 'C#'], 'names');
    Assert::equals('colors', $l->get(['red', 'green', 'blue']));
    Assert::equals('names', $l->get(['PHP', 'Java', 'C#']));
  }
}