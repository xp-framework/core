<?php namespace lang\unittest;

use io\streams\{MemoryOutputStream, Streams};
use lang\{Error, IllegalArgumentException, Throwable, XPClass, XPException};
use test\{Action, Assert, Expect, Test};

class ExceptionsTest {

  #[Test]
  public function noException() {
    try {
      // Nothing
    } catch (Throwable $caught) {
      $this->fail('Caught an exception but none where thrown', $caught);
    }
  }

  #[Test]
  public function thrownExceptionCaught() {
    try {
      throw new Throwable('Test');
    } catch (Throwable $caught) {
      Assert::instance(Throwable::class, $caught);
      unset($caught);
      return true;
    }

    $this->fail('Thrown Exception not caught');
  }

  #[Test]
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

  #[Test]
  public function exceptions_have_non_empty_stracktraces() {
    $trace= (new Throwable('Test'))->getStackTrace();
    Assert::instance('lang.StackTraceElement[]', $trace);
    Assert::notEquals(0, sizeof($trace));
  }

  #[Test]
  public function first_frame_contains_this_class_and_method() {
    $first= (new Throwable('Test'))->getStackTrace()[0];
    
    Assert::equals(
      ['class' => self::class, 'method' => __FUNCTION__],
      ['class' => $first->class, 'method' => $first->method]
    );
  }

  #[Test]
  public function an_exception_equals_itself() {
    $e= new Throwable('Test');
    Assert::equals($e, $e);
  }

  #[Test]
  public function all_exceptions_are_unique() {
    Assert::notEquals(new Throwable('Test'), new Throwable('Test'));
  }

  #[Test]
  public function exceptions_hashcodes_are_also_unique() {
    Assert::notEquals(
      (new Throwable('Test'))->hashCode(),
      (new Throwable('Test'))->hashCode()
    );
  }

  #[Test]
  public function message() {
    Assert::equals('Test', (new Throwable('Test'))->getMessage());
  }

  #[Test]
  public function cause() {
    $cause= new Throwable('Cause');
    Assert::equals($cause, (new Throwable('Test', $cause))->getCause());
  }

  #[Test]
  public function cause_is_optional() {
    Assert::null((new Throwable('Test'))->getCause());
  }

  #[Test]
  public function cause_can_be_modified() {
    $cause= new Throwable('Cause');
    $e= new Throwable('Test');
    $e->setCause($cause);
    Assert::equals($cause, $e->getCause());
  }

  #[Test]
  public function compoundMessage() {
    Assert::equals(
      'Exception lang.Throwable (Test)', 
      (new Throwable('Test'))->compoundMessage()
    );
  }

  #[Test]
  public function printStackTrace() {
    $out= new MemoryOutputStream();
    $e= new Throwable('Test');
    $e->printStackTrace(Streams::writeableFd($out));
    Assert::equals($e->toString(), $out->bytes());
  }
  
  #[Test, Expect(IllegalArgumentException::class)]
  public function withCause_must_be_a_throwable() {
    new XPException('Message', 'Anything...');
  }

  #[Test]
  public function wrap_xp_exceptions() {
    $e= new XPException('Test');
    Assert::equals($e, Throwable::wrap($e));
  }

  #[Test]
  public function wrap_php_exceptions() {
    $e= new \TypeError('Test');
    Assert::instance(Error::class, Throwable::wrap($e));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function wrap_non_exceptions() {
    Throwable::wrap($this);
  }

  #[Test]
  public function wrapping_native_exceptions_adds_stacktrace_with_file_and_line() {
    $first= Throwable::wrap(new \Exception('Test'))->getStackTrace()[0];
    Assert::equals([__FILE__, __LINE__ - 1], [$first->file, $first->line]);
  }
}