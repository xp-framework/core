<?php namespace net\xp_framework\unittest\core;

use io\streams\{Streams, MemoryOutputStream};
use lang\{Throwable, Error, XPException, XPClass, IllegalArgumentException};
use unittest\actions\RuntimeVersion;

class ExceptionsTest extends \unittest\TestCase {

  #[@test]
  public function noException() {
    try {
      // Nothing
    } catch (Throwable $caught) {
      $this->fail('Caught an exception but none where thrown', $caught);
    }
  }

  #[@test]
  public function thrownExceptionCaught() {
    try {
      throw new Throwable('Test');
    } catch (Throwable $caught) {
      $this->assertInstanceOf(Throwable::class, $caught);
      unset($caught);
      return true;
    }

    $this->fail('Thrown Exception not caught');
  }

  #[@test]
  public function multipleCatches() {
    try {
      throw new XPException('Test');
    } catch (IllegalArgumentException $caught) {
      return $this->fail('Exception should have been caught in Exception block', 'IllegalArgumentException');
    } catch (XPException $caught) {
      return true;
    } catch (Throwable $caught) {
      return $this->fail('Exception should have been caught in Exception block', 'Throwable');
    }

    $this->fail('Thrown Exception not caught');
  }

  #[@test]
  public function exceptions_have_non_empty_stracktraces() {
    $trace= (new Throwable('Test'))->getStackTrace();
    $this->assertInstanceOf('lang.StackTraceElement[]', $trace);
    $this->assertNotEquals(0, sizeof($trace));
  }

  #[@test]
  public function first_frame_contains_this_class_and_method() {
    $first= (new Throwable('Test'))->getStackTrace()[0];
    
    $this->assertEquals(
      ['class' => self::class, 'method' => __FUNCTION__],
      ['class' => $first->class, 'method' => $first->method]
    );
  }

  #[@test]
  public function an_exception_equals_itself() {
    $e= new Throwable('Test');
    $this->assertEquals($e, $e);
  }

  #[@test]
  public function all_exceptions_are_unique() {
    $this->assertNotEquals(new Throwable('Test'), new Throwable('Test'));
  }

  #[@test]
  public function exceptions_hashcodes_are_also_unique() {
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
  public function cause() {
    $cause= new Throwable('Cause');
    $this->assertEquals($cause, (new Throwable('Test', $cause))->getCause());
  }

  #[@test]
  public function cause_is_optional() {
    $this->assertNull((new Throwable('Test'))->getCause());
  }

  #[@test]
  public function cause_can_be_modified() {
    $cause= new Throwable('Cause');
    $e= new Throwable('Test');
    $e->setCause($cause);
    $this->assertEquals($cause, $e->getCause());
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
    $e->printStackTrace(Streams::writeableFd($out));
    $this->assertEquals($e->toString(), $out->bytes());
  }
  
  #[@test, @expect(IllegalArgumentException::class)]
  public function withCause_must_be_a_throwable() {
    new XPException('Message', 'Anything...');
  }

  #[@test]
  public function wrap_xp_exceptions() {
    $e= new XPException('Test');
    $this->assertEquals($e, Throwable::wrap($e));
  }

  #[@test]
  public function wrap_php5_exceptions() {
    $e= new \Exception('Test');
    $this->assertInstanceOf(XPException::class, Throwable::wrap($e));
  }

  #[@test, @action(new RuntimeVersion('>=7.0.0'))]
  public function wrap_php7_exceptions() {
    $e= new \TypeError('Test');
    $this->assertInstanceOf(Error::class, Throwable::wrap($e));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function wrap_non_exceptions() {
    Throwable::wrap($this);
  }

  #[@test]
  public function wrapping_native_exceptions_adds_stacktrace_with_file_and_line() {
    $first= Throwable::wrap(new \Exception('Test'))->getStackTrace()[0];
    $this->assertEquals([__FILE__, __LINE__ - 1], [$first->file, $first->line]);
  }
}