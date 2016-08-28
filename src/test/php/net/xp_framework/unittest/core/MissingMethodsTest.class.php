<?php namespace net\xp_framework\unittest\core;

use lang\ClassLoader;
use lang\Object;
use lang\Error;

/**
 * Verifies lang.Object's `__call()` implementation
 *
 * @see   https://github.com/xp-framework/xp-framework/issues/133
 */
class MissingMethodsTest extends \unittest\TestCase {

  /** @param var $arg */
  private function callRunOn($arg) {
    $f= [$arg, 'run'];
    $f();
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingMethodInvocation() {
    $this->callRunOn(new Object());
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingParentMethodInvocation() {
    $c= ClassLoader::defineClass('MissingMethodsTest_Fixture', Object::class, [], '{
      public function run() {
        parent::run();
      }
    }');
    $this->callRunOn($c->newInstance());
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method .+::run()/')]
  public function missingParentParentMethodInvocation() {
    $b= ClassLoader::defineClass('MissingMethodsTest_BaseFixture', Object::class, [], '{}');
    $c= ClassLoader::defineClass('MissingMethodsTest_ChildFixture', $b->getName(), [], '{
      public function run() {
        parent::run();
      }
    }');
    $this->callRunOn($c->newInstance());
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingParentPassMethodInvocation() {
    $b= ClassLoader::defineClass('MissingMethodsTest_PassBaseFixture', Object::class, [], '{
      public function run() {
        parent::run();
      }
    }');
    $c= ClassLoader::defineClass('MissingMethodsTest_PassChildFixture', $b->getName(), [], '{
      public function run() {
        parent::run();
      }
    }');
    $this->callRunOn($c->newInstance());
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingStaticParentMethodInvocation() {
    $c= ClassLoader::defineClass('MissingMethodsTest_StaticFixture', Object::class, [], '{
      public static function run() {
        parent::run();
      }
    }');
    $this->callRunOn($c->literal());
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method .+::run()/')]
  public function missingStaticParentParentMethodInvocation() {
    $b= ClassLoader::defineClass('MissingMethodsTest_StaticBaseFixture', Object::class, [], '{}');
    $c= ClassLoader::defineClass('MissingMethodsTest_StaticChildFixture', $b->getName(), [], '{
      public static function run() {
        parent::run();
      }
    }');
    $this->callRunOn($c->literal());
  }

  #[@test, @expect(class= Error::class, withMessage= '/Call to undefined method lang.Object::run()/')]
  public function missingStaticParentPassMethodInvocation() {
    $b= ClassLoader::defineClass('MissingMethodsTest_StaticPassBaseFixture', Object::class, [], '{
      public static function run() {
        parent::run();
      }
    }');
    $c= ClassLoader::defineClass('MissingMethodsTest_StaticPassChildFixture', $b->getName(), [], '{
      public static function run() {
        parent::run();
      }
    }');
    $this->callRunOn($c->literal());
  }
}
