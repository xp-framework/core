<?php namespace net\xp_framework\unittest\core;

use lang\{ClassLoader, Closeable, IllegalStateException};
use net\xp_framework\unittest\Name;
use unittest\{BeforeClass, Test, TestCase, Values};

/**
 * Tests `with()` functionality
 */
class WithTest extends TestCase {
  private static $closes, $raises, $dispose;

  #[BeforeClass]
  public static function defineCloseableSubclasses() {
    self::$closes= ClassLoader::defineClass('_WithTest_C0', null, [Closeable::class], '{
      public $closed= false;
      public function close() { $this->closed= true; }
    }');
    self::$raises= ClassLoader::defineClass('_WithTest_C1', null, [Closeable::class], '{
      private $throwable;
      public function __construct($class) { $this->throwable= $class; }
      public function close() { throw new $this->throwable("Cannot close"); }
    }');
    self::$dispose= ClassLoader::defineClass('_WithTest_C2', null, [\IDisposable::class], '{
      public $disposed= false;
      public function __dispose() { $this->disposed= true; }
    }');
  }

  #[Test]
  public function backwards_compatible_usage_without_closure() {
    with ($f= new Name('test')); {
      $this->assertInstanceOf(Name::class, $f);
    }
  }

  #[Test]
  public function new_usage_with_closure() {
    with (new Name('test'), function($f) {
      $this->assertInstanceOf(Name::class, $f);
    });
  }

  #[Test]
  public function closeable_is_open_inside_block() {
    with (self::$closes->newInstance(), function($f) {
      $this->assertFalse($f->closed);
    });
  }

  #[Test]
  public function closeable_is_closed_after_block() {
    $f= self::$closes->newInstance();
    with ($f, function() {
      // NOOP
    });
    $this->assertTrue($f->closed);
  }

  #[Test]
  public function disposable_is_disposed_after_block() {
    $f= self::$dispose->newInstance();
    with ($f, function() {
      // NOOP
    });
    $this->assertTrue($f->disposed);
  }

  #[Test]
  public function all_closeables_are_closed_after_block() {
    $a= self::$closes->newInstance();
    $b= self::$closes->newInstance();
    with ($a, $b, function() {
      // NOOP
    });
    $this->assertEquals([true, true], [$a->closed, $b->closed]);
  }

  #[Test]
  public function all_closeables_are_closed_after_exception() {
    $a= self::$closes->newInstance();
    $b= self::$closes->newInstance();
    try {
      with ($a, $b, function() {
        throw new IllegalStateException('Test');
      });
      $this->fail('No exception thrown', null, 'lang.IllegalStateException');
    } catch (IllegalStateException $expected) {
      $this->assertEquals([true, true], [$a->closed, $b->closed]);
    }
  }

  #[Test]
  public function exceptions_from_close_are_ignored() {
    with (self::$raises->newInstance(IllegalStateException::class), function() {
      // NOOP
    });
  }

  #[Test, Values([IllegalStateException::class, 'Exception'])]
  public function exceptions_from_close_are_ignored_and_subsequent_closes_executed($class) {
    $b= self::$closes->newInstance();
    with (self::$raises->newInstance($class), $b, function() {
      // NOOP
    });
    $this->assertTrue($b->closed);
  }

  #[Test]
  public function usage_with_closure_returns_whatever_closure_returns() {
    $this->assertEquals('Test', with (function() { return 'Test'; }));
  }
}