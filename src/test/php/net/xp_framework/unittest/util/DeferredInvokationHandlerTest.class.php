<?php namespace net\xp_framework\unittest\util;

use util\AbstractDeferredInvokationHandler;
use util\DeferredInitializationException;
use lang\Runnable;
use lang\IllegalStateException;

/**
 * TestCase for AbstractDeferredInvokationHandler
 */
class DeferredInvokationHandlerTest extends \unittest\TestCase {

  #[@test]
  public function echo_runnable_invokation() {
    $handler= new class() extends AbstractDeferredInvokationHandler {
      public function initialize() {
        return newinstance(Runnable::class, [], [
          'run' => function(...$args) { return $args; }
        ]);
      }
    };
    $args= [1, 2, 'Test'];
    $this->assertEquals($args, $handler->invoke($this, 'run', $args));
  }

  #[@test, @expect(class= IllegalStateException::class, withMessage= 'Test')]
  public function throwing_runnable_invokation() {
    $handler= new class() extends AbstractDeferredInvokationHandler {
      public function initialize() {
        return newinstance(Runnable::class, [], [
          'run' => function(...$args) { throw new \lang\IllegalStateException($args[0]); }
        ]);
      }
    };
    $handler->invoke($this, 'run', ['Test']);
  }

  #[@test, @expect(class= DeferredInitializationException::class, withMessage= 'run')]
  public function initialize_returns_null() {
    $handler= new class() extends AbstractDeferredInvokationHandler {
      public function initialize() {
        return null;
      }
    };
    $handler->invoke($this, 'run', []);
  }

  #[@test, @expect(class= DeferredInitializationException::class, withMessage= 'run')]
  public function initialize_throws_exception() {
    $handler= new class() extends AbstractDeferredInvokationHandler {
      public function initialize() {
        throw new IllegalStateException('Cannot initialize yet');
      }
    };
    $handler->invoke($this, 'run', []);
  }

  #[@test]
  public function initialize_not_called_again_after_success() {
    $handler= new class() extends AbstractDeferredInvokationHandler {
      public $actions = [];
      public function __construct() {
        $this->actions= [
          function() { return newinstance(Runnable::class, [], ['run' => function() { return true; }]); },
          function() { throw new IllegalStateException('Initialization called again'); },
        ];
      }
      public function initialize() {
        $f= array_shift($this->actions);
        return $f();
      }
    };
    $this->assertEquals(true, $handler->invoke($this, 'run', []));
    $this->assertEquals(true, $handler->invoke($this, 'run', []));
  }  

  #[@test]
  public function initialize_called_again_after_failure() {
    $handler= new class() extends AbstractDeferredInvokationHandler {
      public $actions = [];
      public function __construct() {
        $this->actions= [
          function() { throw new IllegalStateException('Error initializing'); },
          function() { return newinstance(Runnable::class, [], ['run' => function() { return true; }]); },
        ];
      }
      public function initialize() {
        $f= array_shift($this->actions);
        return $f();
      }
    };
    try {
      $handler->invoke($this, 'run', []);
    } catch (DeferredInitializationException $expected) {
      // OK
    }
    $this->assertEquals(true, $handler->invoke($this, 'run', []));
  }
}
