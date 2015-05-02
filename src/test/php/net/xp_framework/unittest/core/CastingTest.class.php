<?php namespace net\xp_framework\unittest\core;

use unittest\TestCase;
use lang\Runnable;

/**
 * Tests cast() functionality
 */
class CastingTest extends TestCase implements Runnable {

  /**
   * Runnable implementation
   *
   */
  public function run() { 
    // Intentionally empty
  }

  #[@test]
  public function newinstance() {
    $runnable= newinstance('lang.Runnable', [], [
      'run' => function() { return 'Test'; }
    ]);
    $this->assertEquals('Test', cast($runnable, 'lang.Runnable')->run());
  }

  #[@test, @expect('lang.ClassCastException')]
  public function null() {
    cast(null, 'lang.Object');
  }

  #[@test, @expect('lang.ClassCastException')]
  public function is_nullsafe_per_default() {
    cast(null, 'lang.Runnable')->run();
  }

  #[@test]
  public function passig_null_allowed_when_nullsafe_set_to_false() {
    $this->assertNull(cast(null, 'lang.Object', false));
  }

  #[@test]
  public function thisClass() {
    $this->assertTrue($this === cast($this, $this->getClass()));
  }

  #[@test]
  public function thisClassName() {
    $this->assertTrue($this === cast($this, $this->getClassName()));
  }

  #[@test]
  public function runnableInterface() {
    $this->assertTrue($this === cast($this, 'lang.Runnable'));
  }

  #[@test]
  public function parentClass() {
    $this->assertTrue($this === cast($this, 'unittest.TestCase'));
  }

  #[@test]
  public function objectClass() {
    $this->assertTrue($this === cast($this, 'lang.Object'));
  }

  #[@test]
  public function genericInterface() {
    $this->assertTrue($this === cast($this, 'lang.Generic'));
  }

  #[@test, @expect('lang.ClassCastException')]
  public function unrelated() {
    cast($this, 'lang.types.String');
  }

  #[@test, @expect('lang.ClassCastException')]
  public function subClass() {
    cast(new \lang\Object(), 'lang.types.String');
  }

  #[@test, @expect('lang.ClassCastException')]
  public function nonExistant() {
    cast($this, '@@NON_EXISTANT_CLASS@@');
  }

  #[@test, @expect('lang.ClassCastException')]
  public function primitive() {
    cast('primitive', 'lang.Object');
  }
}
