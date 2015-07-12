<?php namespace net\xp_framework\unittest\util;

use unittest\TestCase;
use util\AbstractDeferredInvokationHandler;


/**
 * TestCase for AbstractDeferredInvokationHandler
 */
class DeferredInvokationHandlerTest extends TestCase {

  #[@test]
  public function echo_runnable_invokation() {
    $handler= newinstance('util.AbstractDeferredInvokationHandler', [], '{
      public function initialize() { 
        return newinstance("lang.Runnable", [], "{
          public function run() {
            return func_get_args();
          }
        }");
      }
    }');
    $args= [1, 2, 'Test'];
    $this->assertEquals($args, $handler->invoke($this, 'run', $args));
  }

  #[@test, @expect(class = 'lang.XPException', withMessage= 'Test')]
  public function throwing_runnable_invokation() {
    $handler= newinstance('util.AbstractDeferredInvokationHandler', [], '{
      public function initialize() { 
        return newinstance("lang.Runnable", [], "{
          public function run() {
            throw new \lang\XPException(func_get_arg(0));
          }
        }");
      }
    }');
    $handler->invoke($this, 'run', ['Test']);
  }

  #[@test, @expect(class = 'util.DeferredInitializationException', withMessage= 'run')]
  public function initialize_returns_null() {
    $handler= newinstance('util.AbstractDeferredInvokationHandler', [], '{
      public function initialize() { 
        return NULL;
      }
    }');
    $handler->invoke($this, 'run', []);
  }

  #[@test, @expect(class = 'util.DeferredInitializationException', withMessage= 'run')]
  public function initialize_throws_exception() {
    $handler= newinstance('util.AbstractDeferredInvokationHandler', [], '{
      public function initialize() { 
        throw new \lang\IllegalStateException("Cannot initialize yet");
      }
    }');
    $handler->invoke($this, 'run', []);
  }

  #[@test]
  public function initialize_not_called_again_after_success() {
    $handler= newinstance('util.AbstractDeferredInvokationHandler', [], '{
      private $actions;
      public function __construct() {
        $this->actions= [
          function() { return newinstance("lang.Runnable", [], "{
            public function run() { return TRUE; }
          }"); },
          function() { throw new \lang\IllegalStateException("Initialization called again"); },
        ];
      }
      public function initialize() {
        $f= array_shift($this->actions);
        return $f();
      }
    }');
    $this->assertEquals(true, $handler->invoke($this, 'run', []));
    $this->assertEquals(true, $handler->invoke($this, 'run', []));
  }  

  #[@test]
  public function initialize_called_again_after_failure() {
    $handler= newinstance('util.AbstractDeferredInvokationHandler', [], '{
      private $actions;
      public function __construct() {
        $this->actions= [
          function() { throw new \lang\IllegalStateException("Error initializing"); },
          function() { return newinstance("lang.Runnable", [], "{
            public function run() { return TRUE; }
          }"); }
        ];
      }
      public function initialize() {
        $f= array_shift($this->actions);
        return $f();
      }
    }');
    try {
      $handler->invoke($this, 'run', []);
    } catch (\util\DeferredInitializationException $expected) {
      // OK
    }
    $this->assertEquals(true, $handler->invoke($this, 'run', []));
  }
}
