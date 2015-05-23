<?php namespace net\xp_framework\unittest\tests;

use lang\ClassLoader;
use lang\XPClass;
use lang\IllegalStateException;
use unittest\TestSuite;
use unittest\TestCase;
use unittest\PrerequisitesNotMetError;

/**
 * Test test actions
 */
class TestActionTest extends TestCase {
  protected $suite;

  /**
   * Setup method. Creates a new test suite.
   */
  public function setUp() {
    $this->suite= new TestSuite();
  }

  #[@test]
  public function beforeTest_and_afterTest_invocation_order() {
    $test= newinstance('unittest.TestCase', ['fixture'], [
      'run' => [],
      '#[@test, @action(new \net\xp_framework\unittest\tests\RecordActionInvocation("run"))] fixture' => function() {
        $this->run[]= 'test';
      }
    ]);
    $this->suite->runTest($test);
    $this->assertEquals(['before', 'test', 'after'], $test->run);
  }

  #[@test]
  public function beforeTest_is_invoked_before_setUp() {
    $test= newinstance('unittest.TestCase', ['fixture'], [
      'run' => [],
      'setUp' => function() {
        $this->run[]= 'setup';
      },
      '#[@test, @action(new \net\xp_framework\unittest\tests\RecordActionInvocation("run"))] fixture' => function() {
        $this->run[]= 'test';
      }
    ]);
    $this->suite->runTest($test);
    $this->assertEquals(['before', 'setup', 'test', 'after'], $test->run);
  }

  #[@test]
  public function afterTest_is_invoked_after_tearDown() {
    $test= newinstance('unittest.TestCase', ['fixture'], [
      'run' => [],
      'tearDown' => function() {
        $this->run[]= 'teardown';
      },
      '#[@test, @action(new \net\xp_framework\unittest\tests\RecordActionInvocation("run"))] fixture' => function() {
        $this->run[]= 'test';
      }
    ]);
    $this->suite->runTest($test);
    $this->assertEquals(['before', 'test', 'teardown', 'after'], $test->run);
  }

  #[@test]
  public function beforeTest_can_skip_test() {
    ClassLoader::defineClass('net.xp_framework.unittest.tests.SkipThisTest', 'lang.Object', ['unittest.TestAction'], [
      'beforeTest' => function(TestCase $t) { throw new PrerequisitesNotMetError('Skip'); },
      'afterTest' => function(TestCase $t) { /* NOOP */ }
    ]);
    $test= newinstance('unittest.TestCase', ['fixture'], [
      '#[@test, @action(new \net\xp_framework\unittest\tests\SkipThisTest())] fixture' => function() {
        throw new IllegalStateException('This test should have been skipped');
      }
    ]);
    $r= $this->suite->runTest($test);
    $this->assertEquals(1, $r->skipCount());
  }

  #[@test]
  public function invocation_order_with_class_annotation() {
    $this->suite->addTestClass(XPClass::forName('net.xp_framework.unittest.tests.TestWithAction'));
    $this->suite->run();
    $this->assertEquals(
      ['before', 'one', 'after', 'before', 'two', 'after'],
      array_merge($this->suite->testAt(0)->run, $this->suite->testAt(1)->run)
    );
  }

  #[@test]
  public function test_action_with_arguments() {
    ClassLoader::defineClass('net.xp_framework.unittest.tests.PlatformVerification', 'lang.Object', ['unittest.TestAction'], '{
      protected $platform;

      public function __construct($platform) {
        $this->platform= $platform;
      }

      public function beforeTest(\unittest\TestCase $t) {
        if (PHP_OS !== $this->platform) {
          throw new \unittest\PrerequisitesNotMetError("Skip", NULL, $this->platform);
        }
      }

      public function afterTest(\unittest\TestCase $t) {
        // NOOP
      }
    }');
    $test= newinstance('unittest.TestCase', ['fixture'], [
      '#[@test, @action(new \net\xp_framework\unittest\tests\PlatformVerification("Test"))] fixture' => function() {
        throw new IllegalStateException('This test should have been skipped');
      }
    ]);
    $outcome= $this->suite->runTest($test)->outcomeOf($test);
    $this->assertInstanceOf('unittest.TestPrerequisitesNotMet', $outcome);
    $this->assertEquals(['Test'], $outcome->reason->prerequisites);
  }

  #[@test]
  public function multiple_actions() {
    $test= newinstance('unittest.TestCase', ['fixture'], '{
      public $one= [], $two= [];

      #[@test, @action([
      #  new \net\xp_framework\unittest\tests\RecordActionInvocation("one"),
      #  new \net\xp_framework\unittest\tests\RecordActionInvocation("two")
      #])]
      public function fixture() {
      }
    }');
    $this->suite->runTest($test);
    $this->assertEquals(
      ['one' => ['before', 'after'], 'two' => ['before', 'after']],
      ['one' =>  $test->one, 'two' => $test->two]
    );
  }

  #[@test]
  public function afterTest_can_raise_AssertionFailedErrors() {
    ClassLoader::defineClass('net.xp_framework.unittest.tests.FailOnTearDown', 'lang.Object', ['unittest.TestAction'], '{
      public function beforeTest(\unittest\TestCase $t) {
        // NOOP
      }

      public function afterTest(\unittest\TestCase $t) {
        throw new \unittest\AssertionFailedError("Skip");
      }
    }');
    $test= newinstance('unittest.TestCase', ['fixture'], [
      '#[@test, @action(new \net\xp_framework\unittest\tests\FailOnTearDown())] fixture' => function() {
        // NOOP
      }
    ]);
    $r= $this->suite->runTest($test);
    $this->assertEquals(1, $r->failureCount());
  }

  #[@test]
  public function all_afterTest_exceptions_are_chained_into_one() {
    ClassLoader::defineClass('net.xp_framework.unittest.tests.FailOnTearDownWith', 'lang.Object', ['unittest.TestAction'], '{
      protected $message;

      public function __construct($message) {
        $this->message= $message;
      }

      public function beforeTest(\unittest\TestCase $t) {
        // NOOP
      }

      public function afterTest(\unittest\TestCase $t) {
        throw new \unittest\AssertionFailedError($this->message);
      }
    }');
    $test= newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action([
      #  new \net\xp_framework\unittest\tests\FailOnTearDownWith("First"),
      #  new \net\xp_framework\unittest\tests\FailOnTearDownWith("Second")
      #])]
      public function fixture() {
        // NOOP
      }
    }');
    $r= $this->suite->runTest($test);
    $outcome= $r->outcomeOf($test);
    $this->assertEquals(['Second', 'First'], [$outcome->reason->getMessage(), $outcome->reason->getCause()->getMessage()]);
  }
}
