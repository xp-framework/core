<?php namespace net\xp_framework\unittest\tests;

use lang\IllegalArgumentException;
use lang\Error;
use lang\MethodNotImplementedException;
use util\NoSuchElementException;
use unittest\TestSuite;
use unittest\actions\RuntimeVersion;

/**
 * Test TestSuite class methods
 *
 * @see    xp://unittest.TestSuite
 */
class SuiteTest extends \unittest\TestCase {
  protected $suite= null;
    
  /**
   * Setup method. Creates a new test suite.
   */
  public function setUp() {
    $this->suite= new TestSuite();
  }

  #[@test]
  public function initallyEmpty() {
    $this->assertEquals(0, $this->suite->numTests());
  }    

  #[@test]
  public function addingATest() {
    $this->suite->addTest($this);
    $this->assertEquals(1, $this->suite->numTests());
  }    

  #[@test]
  public function addingATestTwice() {
    $this->suite->addTest($this);
    $this->suite->addTest($this);
    $this->assertEquals(2, $this->suite->numTests());
  }    

  #[@test, @expect(IllegalArgumentException::class), @action(new RuntimeVersion('<7.0.0-dev'))]
  public function addNonTest() {
    $this->suite->addTest(new \lang\Object());
  }

  #[@test, @expect(Error::class), @action(new RuntimeVersion('>=7.0.0-dev'))]
  public function addNonTest7() {
    $this->suite->addTest(new \lang\Object());
  }

  #[@test, @expect(IllegalArgumentException::class), @action(new RuntimeVersion('<7.0.0-dev'))]
  public function runNonTest() {
    $this->suite->runTest(new \lang\Object());
  }

  #[@test, @expect(Error::class), @action(new RuntimeVersion('>=7.0.0-dev'))]
  public function runNonTest7() {
    $this->suite->runTest(new \lang\Object());
  }

  #[@test, @expect(MethodNotImplementedException::class)]
  public function addInvalidTest() {
    $this->suite->addTest(newinstance('unittest.TestCase', ['nonExistant'], '{}'));
  }

  #[@test, @expect(MethodNotImplementedException::class)]
  public function runInvalidTest() {
    $this->suite->runTest(newinstance('unittest.TestCase', ['nonExistant'], '{}'));
  }

  #[@test]
  public function adding_a_testclass_returns_ignored_methods() {
    $class= \lang\XPClass::forName('net.xp_framework.unittest.tests.SimpleTestCase');
    $ignored= $this->suite->addTestClass($class);
    $this->assertEquals([$class->getMethod('ignored')], $ignored);
  }

  #[@test]
  public function adding_a_testclass_fills_suites_tests() {
    $class= \lang\XPClass::forName('net.xp_framework.unittest.tests.SimpleTestCase');
    $this->suite->addTestClass($class);
    for ($i= 0, $s= $this->suite->numTests(); $i < $s; $i++) {
      $this->assertInstanceOf('unittest.TestCase', $this->suite->testAt($i));
    }
  }

  #[@test]
  public function adding_a_testclass_twice_fills_suites_tests_twice() {
    $class= \lang\XPClass::forName('net.xp_framework.unittest.tests.SimpleTestCase');
    $this->suite->addTestClass($class);
    $n= $this->suite->numTests();
    $this->suite->addTestClass($class);
    $this->assertEquals($n * 2, $this->suite->numTests());
  }

  #[@test, @expect(NoSuchElementException::class)]
  public function addingEmptyTest() {
    $this->suite->addTestClass(\lang\XPClass::forName('net.xp_framework.unittest.tests.EmptyTestCase'));
  }    

  #[@test]
  public function addingEmptyTestAfter() {
    $this->suite->addTestClass(\lang\XPClass::forName('net.xp_framework.unittest.tests.SimpleTestCase'));
    $before= $this->suite->numTests();
    try {
      $this->suite->addTestClass(\lang\XPClass::forName('net.xp_framework.unittest.tests.EmptyTestCase'));
      $this->fail('Expected exception not thrown', null, 'util.NoSuchElementException');
    } catch (\util\NoSuchElementException $expected) { 
    }
    $this->assertEquals($before, $this->suite->numTests());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function addingANonTestClass() {
    $this->suite->addTestClass(\lang\XPClass::forName('lang.Object'));
  }    

  #[@test]
  public function clearingTests() {
    $this->suite->addTest($this);
    $this->assertEquals(1, $this->suite->numTests());
    $this->suite->clearTests();
    $this->assertEquals(0, $this->suite->numTests());
  }

  #[@test]
  public function runningASingleSucceedingTest() {
    $r= $this->suite->runTest(new SimpleTestCase('succeeds'));
    $this->assertInstanceOf('unittest.TestResult', $r);
    $this->assertEquals(1, $r->count(), 'count');
    $this->assertEquals(1, $r->runCount(), 'runCount');
    $this->assertEquals(1, $r->successCount(), 'successCount');
    $this->assertEquals(0, $r->failureCount(), 'failureCount');
    $this->assertEquals(0, $r->skipCount(), 'skipCount');
  }    

  #[@test]
  public function runningASingleFailingTest() {
    $r= $this->suite->runTest(new SimpleTestCase('fails'));
    $this->assertInstanceOf('unittest.TestResult', $r);
    $this->assertEquals(1, $r->count(), 'count');
    $this->assertEquals(1, $r->runCount(), 'runCount');
    $this->assertEquals(0, $r->successCount(), 'successCount');
    $this->assertEquals(1, $r->failureCount(), 'failureCount');
    $this->assertEquals(0, $r->skipCount(), 'skipCount');
  }    

  #[@test]
  public function runMultipleTests() {
    $this->suite->addTest(new SimpleTestCase('fails'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $this->suite->addTest(new SimpleTestCase('skipped'));
    $this->suite->addTest(new SimpleTestCase('ignored'));
    $r= $this->suite->run();
    $this->assertInstanceOf('unittest.TestResult', $r);
    $this->assertEquals(4, $r->count(), 'count');
    $this->assertEquals(2, $r->runCount(), 'runCount');
    $this->assertEquals(1, $r->successCount(), 'successCount');
    $this->assertEquals(1, $r->failureCount(), 'failureCount');
    $this->assertEquals(2, $r->skipCount(), 'skipCount');
  }    

  #[@test]
  public function runInvokesBeforeClassOneClass() {
    SimpleTestCase::$init= 0;
    $this->suite->addTest(new SimpleTestCase('fails'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $this->suite->run();
    $this->assertEquals(1, SimpleTestCase::$init);
  }

  #[@test]
  public function runInvokesBeforeClassMultipleClasses() {
    SimpleTestCase::$init= 0;
    $this->suite->addTest(new SimpleTestCase('fails'));
    $this->suite->addTest(new AnotherTestCase('succeeds'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $this->suite->run();
    $this->assertEquals(1, SimpleTestCase::$init);
  }

  #[@test]
  public function runTestInvokesBeforeClass() {
    SimpleTestCase::$init= 0;
    $this->suite->runTest(new SimpleTestCase('succeeds'));
    $this->assertEquals(1, SimpleTestCase::$init);
  }    

  #[@test]
  public function beforeClassFails() {
    SimpleTestCase::$init= -1;
    $this->suite->addTest(new SimpleTestCase('fails'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $this->suite->addTest(new AnotherTestCase('succeeds'));
    $this->suite->addTest(new SimpleTestCase('skipped'));
    $this->suite->addTest(new SimpleTestCase('ignored'));
    $r= $this->suite->run();
    $this->assertEquals(4, $r->skipCount(), 'skipCount');
    $this->assertEquals(1, $r->successCount(), 'successCount');
  }    

  #[@test]
  public function beforeClassRaisesAPrerequisitesNotMet() {
    $t= newinstance('unittest.TestCase', ['irrelevant'], '{
      #[@beforeClass]
      public static function raise() {
        throw new \unittest\PrerequisitesNotMetError("Cannot run");
      }
      
      #[@test]
      public function irrelevant() {
        $this->assertEquals(1, 0);
      }
    }');
    $this->suite->addTest($t);
    $r= $this->suite->run();
    $this->assertEquals(1, $r->skipCount(), 'skipCount');
    $this->assertInstanceOf('unittest.TestPrerequisitesNotMet', $r->outcomeOf($t));
    $this->assertInstanceOf('unittest.PrerequisitesNotMetError', $r->outcomeOf($t)->reason);
    $this->assertEquals('Cannot run', $r->outcomeOf($t)->reason->getMessage());
  }    

  #[@test]
  public function beforeClassRaisesAnException() {
    $t= newinstance('unittest.TestCase', ['irrelevant'], '{
      #[@beforeClass]
      public static function raise() {
        throw new \lang\IllegalStateException("Skip");
      }
      
      #[@test]
      public function irrelevant() {
        $this->assertEquals(1, 0);
      }
    }');
    $this->suite->addTest($t);
    $r= $this->suite->run();
    $this->assertEquals(1, $r->skipCount(), 'skipCount');
    $this->assertInstanceOf('unittest.TestPrerequisitesNotMet', $r->outcomeOf($t));
    $this->assertInstanceOf('unittest.PrerequisitesNotMetError', $r->outcomeOf($t)->reason);
    $this->assertEquals('Exception in beforeClass method raise', $r->outcomeOf($t)->reason->getMessage());
  }    

  #[@test]
  public function runInvokesAfterClass() {
    SimpleTestCase::$dispose= 0;
    $this->suite->addTest(new SimpleTestCase('fails'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $this->suite->run();
    $this->assertEquals(1, SimpleTestCase::$dispose);
  }    

  #[@test]
  public function runTestInvokesAfterClass() {
    SimpleTestCase::$dispose= 0;
    $this->suite->runTest(new SimpleTestCase('succeeds'));
    $this->assertEquals(1, SimpleTestCase::$dispose);
  }    

  #[@test]
  public function warningsMakeTestFail() {
    with ($test= new SimpleTestCase('raisesAnError')); {
      $this->assertEquals(
        ['"Test error" in ::trigger_error() (SimpleTestCase.class.php, line 69, occured once)'],
        $this->suite->runTest($test)->failed[$test->hashCode()]->reason
      );
    }
  }

  #[@test]
  public function exceptionsMakeTestFail() {
    with ($test= new SimpleTestCase('throws')); {
      $this->assertInstanceOf(
        'lang.IllegalArgumentException',
        $this->suite->runTest($test)->failed[$test->hashCode()]->reason
      );
    }
  }

  #[@test]
  public function expectedExceptionsWithWarningsMakeTestFail() {
    with ($test= new SimpleTestCase('catchExpectedWithWarning')); {
      $this->assertEquals(
        ['"Test error" in ::trigger_error() (SimpleTestCase.class.php, line 123, occured once)'],
        $this->suite->runTest($test)->failed[$test->hashCode()]->reason
      );
    }
  }
  
  #[@test]
  public function warningsDontAffectSucceedingTests() {
    $this->suite->addTest(new SimpleTestCase('raisesAnError'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->failureCount());
    $this->assertEquals(1, $r->successCount());
  }
 
  #[@test]
  public function warningsFromFailuresDontAffectSucceedingTests() {
    $this->suite->addTest(new SimpleTestCase('raisesAnErrorAndFails'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->failureCount());
    $this->assertEquals(1, $r->successCount());
  }

  #[@test]
  public function warningsFromSetupDontAffectSucceedingTests() {
    $this->suite->addTest(new SimpleTestCase('raisesAnErrorInSetup'));
    $this->suite->addTest(new SimpleTestCase('succeeds'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->successCount());
  }

  #[@test]
  public function expectedException() {
    $this->suite->addTest(new SimpleTestCase('catchExpected'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->successCount());
  }

  #[@test]
  public function subclassOfExpectedException() {
    $this->suite->addTest(new SimpleTestCase('catchSubclassOfExpected'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->successCount());
  }

  #[@test]
  public function expectedExceptionNotThrown() {
    $this->suite->addTest(new SimpleTestCase('expectedExceptionNotThrown'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->failureCount());
    $this->assertEquals(
      'Caught Exception lang.FormatException (Test) instead of expected lang.IllegalArgumentException', 
      cast($r->outcomeOf($this->suite->testAt(0)), 'unittest.TestFailure')->reason->getMessage()
    );
  }

  #[@test]
  public function catchExpectedWithMessage() {
    $this->suite->addTest(new SimpleTestCase('catchExpectedWithMessage'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->successCount());
  }

  #[@test]
  public function catchExpectedWithMismatchingMessage() {
    $this->suite->addTest(new SimpleTestCase('catchExpectedWithWrongMessage'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->failureCount());
    $this->assertEquals(
      'Expected lang.IllegalArgumentException\'s message "Another message" differs from expected /Hello/',
      cast($r->outcomeOf($this->suite->testAt(0)), 'unittest.TestFailure')->reason->getMessage()
    );
  }

  #[@test]
  public function catchExpectedWithPatternMessage() {
    $this->suite->addTest(new SimpleTestCase('catchExpectedWithPatternMessage'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->successCount());
  }

  #[@test]
  public function catchExpectedWithEmptyMessage() {
    $this->suite->addTest(newinstance('unittest.TestCase', ['fixture'], [
      '#[@test, @expect(class= "lang.IllegalArgumentException", withMessage= "")] fixture' => function() {
        throw new IllegalArgumentException('');
      }
    ]));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->successCount());
  }

  #[@test]
  public function catchExceptionsDuringSetUpOfTestDontBringDownTestSuite() {
    $this->suite->addTest(new SetUpFailingTestCase('emptyTest'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->failureCount());
  }

  #[@test]
  public function doFail() {
    $this->suite->addTest(new SimpleTestCase('doFail'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->failureCount());
  }

  #[@test]
  public function doSkip() {
    $this->suite->addTest(new SimpleTestCase('doSkip'));
    $r= $this->suite->run();
    $this->assertEquals(1, $r->skipCount());
  }
}
