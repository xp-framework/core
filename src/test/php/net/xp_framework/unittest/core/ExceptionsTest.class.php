<?php namespace net\xp_framework\unittest\core;

use unittest\TestCase;
use io\streams\Streams;
use io\streams\MemoryOutputStream;
use lang\Throwable;
use unittest\actions\RuntimeVersion;

/**
 * Test the XP exception mechanism
 */
class ExceptionsTest extends TestCase {

  #[@test]
  public function noException() {
    try {
      // Nothing
    } catch (\lang\XPException $caught) {
      $this->fail('Caught an exception but none where thrown', $caught);
    }
  }

  #[@test]
  public function thrownExceptionCaught() {
    try {
      throw new Throwable('Test');
    } catch (Throwable $caught) {
      $this->assertInstanceOf('lang.Throwable', $caught);
      unset($caught);
      return true;
    }

    $this->fail('Thrown Exception not caught');
  }

  #[@test]
  public function multipleCatches() {
    try {
      throw new \lang\XPException('Test');
    } catch (\lang\IllegalArgumentException $caught) {
      return $this->fail('Exception should have been caught in Exception block', 'IllegalArgumentException');
    } catch (\lang\XPException $caught) {
      return true;
    } catch (Throwable $caught) {
      return $this->fail('Exception should have been caught in Exception block', 'Throwable');
    }

    $this->fail('Thrown Exception not caught');
  }

  #[@test]
  public function stackTrace() {
    $trace= (new Throwable('Test'))->getStackTrace();
    $this->assertInstanceOf('lang.StackTraceElement[]', $trace);
    $this->assertNotEquals(0, sizeof($trace));
  }

  #[@test]
  public function firstFrame() {
    $trace= (new Throwable('Test'))->getStackTrace();
    
    $this->assertEquals(get_class($this), $trace[0]->class);
    $this->assertEquals($this->getName(), $trace[0]->method);
    $this->assertEquals(NULL, $trace[0]->file);
    $this->assertEquals(0, $trace[0]->line);
    $this->assertEquals([], $trace[0]->args);
    $this->assertEquals('', $trace[0]->message);
  }

  #[@test]
  public function allExceptionsAreUnique() {
    $this->assertNotEquals(new Throwable('Test'), new Throwable('Test'));
  }

  #[@test]
  public function hashCodesAreUnique() {
    $this->assertNotEquals(
      (new Throwable('Test'))->hashCode(),
      (new Throwable('Test'))->hashCode()
    );
  }

  #[@test]
  public function message() {
    $this->assertEquals('Test', (new Throwable('Test'))->getMessage());
  }

  #[@test]
  public function classMethod() {
    $this->assertEquals(\lang\XPClass::forName('lang.Throwable'), (new Throwable('Test'))->getClass());
  }

  /** @deprecated */
  #[@test]
  public function classNameMethod() {
    $this->assertEquals('lang.Throwable', (new Throwable('Test'))->getClassName());
  }

  #[@test]
  public function compoundMessage() {
    $this->assertEquals(
      'Exception lang.Throwable (Test)', 
      (new Throwable('Test'))->compoundMessage()
    );
  }

  #[@test]
  public function printStackTrace() {
    $out= new MemoryOutputStream();
    $e= new Throwable('Test');
    create($e)->printStackTrace(Streams::writeableFd($out));
    $this->assertEquals($e->toString(), $out->getBytes());
  }
  
  #[@test, @expect('lang.IllegalArgumentException'), @action(new RuntimeVersion('<7.0.0-dev'))]
  public function withCause_must_be_a_throwable() {
    new \lang\XPException('Message', 'Anything...');
  }

  #[@test, @expect('lang.Error'), @action(new RuntimeVersion('>=7.0.0-dev'))]
  public function withCause_must_be_a_throwable7() {
    new \lang\XPException('Message', 'Anything...');
  }
}
