<?php namespace lang\unittest;

use lang\IllegalArgumentException;
use net\xp_framework\unittest\Name;
use unittest\{Assert, Expect, Test};

class ArrayTest {

  #[Test]
  public function primitiveStringArrayValue() {
    $name= new Name('test');
    $l= create('new lang.unittest.Lookup<string, string[]>', [
      'name' => [$name]
    ]);
    Assert::equals([$name], $l->get('name'));
  }

  #[Test]
  public function primitiveStringArrayKey() {
    $l= create('new lang.unittest.Lookup<string[], lang.Value>');
    $name= new Name('test');
    $l->put(['name'], $name);
    Assert::equals($name, $l->get(['name']));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function stringToArrayOfStringInvalid() {
    create('new lang.unittest.Lookup<string, string[]>')
      ->put('greeting', ['Hello', 'World', '!!!', 1])
    ;
  }

  #[Test]
  public function stringToArrayOfStringMultiple() {
    $l= create('new lang.unittest.Lookup<string, string[]>', [
      'colors' => ['red', 'green', 'blue'],
      'names'  => ['PHP', 'Java', 'C#']
    ]);
    Assert::equals(['red', 'green', 'blue'], $l->get('colors'));
    Assert::equals(['PHP', 'Java', 'C#'], $l->get('names'));
  }
 
  #[Test]
  public function arrayOfStringToStringMultiple() {
    $l= create('new lang.unittest.Lookup<string[], string>');
    $l->put(['red', 'green', 'blue'], 'colors');
    $l->put(['PHP', 'Java', 'C#'], 'names');
    Assert::equals('colors', $l->get(['red', 'green', 'blue']));
    Assert::equals('names', $l->get(['PHP', 'Java', 'C#']));
  }
}