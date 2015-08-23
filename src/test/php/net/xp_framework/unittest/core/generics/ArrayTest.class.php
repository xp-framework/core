<?php namespace net\xp_framework\unittest\core\generics;

use lang\IllegalArgumentException;

/**
 * TestCase for generic behaviour at runtime.
 *
 * @see   xp://net.xp_framework.unittest.core.generics.Lookup
 */
class ArrayTest extends \unittest\TestCase {

  #[@test]
  public function primitiveStringArrayValue() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, string[]>', [
      'this' => [$this->name]
    ]);
    $this->assertEquals([$this->name], $l->get('this'));
  }

  #[@test]
  public function primitiveStringArrayKey() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string[], unittest.TestCase>');
    $l->put(['this'], $this);
    $this->assertEquals($this, $l->get(['this']));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function stringToArrayOfStringInvalid() {
    create('new net.xp_framework.unittest.core.generics.Lookup<string, string[]>')
      ->put('greeting', ['Hello', 'World', '!!!', 1])
    ;
  }

  #[@test]
  public function stringToArrayOfStringMultiple() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string, string[]>', [
      'colors' => ['red', 'green', 'blue'],
      'names'  => ['PHP', 'Java', 'C#']
    ]);
    $this->assertEquals(['red', 'green', 'blue'], $l->get('colors'));
    $this->assertEquals(['PHP', 'Java', 'C#'], $l->get('names'));
  }
 
  #[@test]
  public function arrayOfStringToStringMultiple() {
    $l= create('new net.xp_framework.unittest.core.generics.Lookup<string[], string>');
    $l->put(['red', 'green', 'blue'], 'colors');
    $l->put(['PHP', 'Java', 'C#'], 'names');
    $this->assertEquals('colors', $l->get(['red', 'green', 'blue']));
    $this->assertEquals('names', $l->get(['PHP', 'Java', 'C#']));
  }
}