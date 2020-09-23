<?php namespace net\xp_framework\unittest\core;

use lang\{ClassCastException, CommandLine, Runnable, Value};
use unittest\{Expect, Test, TestCase, Values};

/**
 * Tests cast() functionality
 */
class CastingTest extends TestCase implements Runnable {

  /** @return void */
  public function run() { 
    // Intentionally empty
  }

  #[Test]
  public function newinstance() {
    $runnable= new class() implements Runnable {
      public function run() { return 'Test'; }
    };
    $this->assertEquals('Test', cast($runnable, Runnable::class)->run());
  }

  #[Test, Expect(ClassCastException::class)]
  public function null() {
    cast(null, Value::class);
  }

  #[Test, Expect(ClassCastException::class)]
  public function is_nullsafe_per_default() {
    cast(null, Runnable::class)->run();
  }

  #[Test]
  public function thisClass() {
    $this->assertTrue($this === cast($this, typeof($this)));
  }

  #[Test]
  public function thisClassName() {
    $this->assertTrue($this === cast($this, nameof($this)));
  }

  #[Test]
  public function thisClassLiteral() {
    $this->assertTrue($this === cast($this, self::class));
  }

  #[Test]
  public function runnableInterface() {
    $this->assertTrue($this === cast($this, Runnable::class));
  }

  #[Test]
  public function parentClass() {
    $this->assertTrue($this === cast($this, TestCase::class));
  }

  #[Test]
  public function selfClass() {
    $this->assertTrue($this === cast($this, self::class));
  }

  #[Test, Expect(ClassCastException::class)]
  public function unrelated() {
    cast($this, CommandLine::class);
  }

  #[Test, Expect(ClassCastException::class)]
  public function nonExistant() {
    cast($this, '@@NON_EXISTANT_CLASS@@');
  }

  #[Test, Expect(ClassCastException::class)]
  public function primitive() {
    cast('primitive', Value::class);
  }

  #[Test, Expect(ClassCastException::class), Values(['', null])]
  public function empty_or_null_name($name) {
    cast($this, $name);
  }

  #[Test, Values([null, 'test'])]
  public function nullable_string($value) {
    $this->assertEquals($value, cast($value, '?string'));
  }

  #[Test, Expect(ClassCastException::class)]
  public function cannot_cast_arrays_to_nullable_string() {
    cast([1], '?string');
  }
}