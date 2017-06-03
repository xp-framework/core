<?php namespace net\xp_framework\unittest\core;

use unittest\TestCase;
use lang\{Runnable, Value, CommandLine, ClassCastException};

/**
 * Tests cast() functionality
 */
class CastingTest extends TestCase implements Runnable {

  /** @return void */
  public function run() { 
    // Intentionally empty
  }

  #[@test]
  public function newinstance() {
    $runnable= new class() implements Runnable {
      public function run() { return 'Test'; }
    };
    $this->assertEquals('Test', cast($runnable, Runnable::class)->run());
  }

  #[@test, @expect(ClassCastException::class)]
  public function null() {
    cast(null, Value::class);
  }

  #[@test, @expect(ClassCastException::class)]
  public function is_nullsafe_per_default() {
    cast(null, Runnable::class)->run();
  }

  #[@test]
  public function passig_null_allowed_when_nullsafe_set_to_false() {
    $this->assertNull(cast(null, Value::class, false));
  }

  #[@test]
  public function thisClass() {
    $this->assertTrue($this === cast($this, typeof($this)));
  }

  #[@test]
  public function thisClassName() {
    $this->assertTrue($this === cast($this, nameof($this)));
  }

  #[@test]
  public function thisClassLiteral() {
    $this->assertTrue($this === cast($this, self::class));
  }

  #[@test]
  public function runnableInterface() {
    $this->assertTrue($this === cast($this, Runnable::class));
  }

  #[@test]
  public function parentClass() {
    $this->assertTrue($this === cast($this, TestCase::class));
  }

  #[@test]
  public function selfClass() {
    $this->assertTrue($this === cast($this, self::class));
  }

  #[@test, @expect(ClassCastException::class)]
  public function unrelated() {
    cast($this, CommandLine::class);
  }

  #[@test, @expect(ClassCastException::class)]
  public function nonExistant() {
    cast($this, '@@NON_EXISTANT_CLASS@@');
  }

  #[@test, @expect(ClassCastException::class)]
  public function primitive() {
    cast('primitive', Value::class);
  }

  #[@test, @expect(ClassCastException::class), @values(['', null])]
  public function empty_or_null_name($name) {
    cast($this, $name);
  }
}
