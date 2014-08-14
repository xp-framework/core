<?php namespace net\xp_framework\unittest\tests;

/**
 * Test VerifyThat class
 */
class VerifyThatTest extends \unittest\TestCase {
  protected $suite= null;

  /**
   * Setup method. Creates a new test suite.
   */
  public function setUp() {
    $this->suite= new \unittest\TestSuite();
  }

  /**
   * Assertion helper: Assert a test succeeds
   *
   * @param  unittest.TestCase $test
   * @throws unittest.AssertionFailedError
   */
  protected function assertSucceeds($test) {
    $outcome= $this->suite->runTest($test)->outcomeOf($test);
    $this->assertInstanceOf('unittest.TestExpectationMet', $outcome, \xp::stringOf($outcome));
  }

  /**
   * Assertion helper: Assert a test is skipped
   *
   * @param  var[] $prerequisites
   * @param  unittest.TestCase $test
   * @throws unittest.AssertionFailedError
   */
  protected function assertSkipped($prerequisites, $test) {
    $outcome= $this->suite->runTest($test)->outcomeOf($test);
    $this->assertInstanceOf('unittest.TestPrerequisitesNotMet', $outcome, \xp::stringOf($outcome));
    $this->assertEquals($prerequisites, $outcome->reason->prerequisites);
  }

  /**
   * Fixture for with_static_method_on_other_class_returning_true()
   *
   * @return bool
   */
  public static function returnTrue() {
    return true;
  }

  #[@test]
  public function with_closure_returning_true() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat(function() { return true; }))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_closure_returning_false() {
    $this->assertSkipped(['<function()>'], newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat(function() { return false; }))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function with_closure_throwing_exception() {
    $this->assertSkipped(['<function()>'], newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat(function() {
      #  throw new \lang\IllegalStateException("Test");
      #}))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }


  #[@test]
  public function with_closure_accessing_member() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      public $member= true;
      #[@test, @action(new \unittest\actions\VerifyThat(function() { return $this->member; }))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_closure_accessing_protected_member() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      protected $member= true;
      #[@test, @action(new \unittest\actions\VerifyThat(function() { return $this->member; }))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_closure_accessing_static_member() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      public static $member= true;
      #[@test, @action(new \unittest\actions\VerifyThat(function() { return self::$member; }))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_closure_accessing_protected_static_member() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      protected static $member= true;
      #[@test, @action(new \unittest\actions\VerifyThat(function() { return self::$member; }))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_method_on_this_returning_true() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      public function returnTrue() { return true; }

      #[@test, @action(new \unittest\actions\VerifyThat("returnTrue"))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_protected_method_on_this_returning_true() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      protected function returnTrue() { return true; }

      #[@test, @action(new \unittest\actions\VerifyThat("returnTrue"))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_method_on_this_returning_false() {
    $this->assertSkipped(['$this->returnFalse'], newinstance('unittest.TestCase', ['fixture'], '{
      public function returnFalse() { return false; }

      #[@test, @action(new \unittest\actions\VerifyThat("returnFalse"))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function with_static_method_on_self_returning_true() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      public static function returnTrue() { return true; }

      #[@test, @action(new \unittest\actions\VerifyThat("self::returnTrue"))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_protected_static_method_on_self_returning_true() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      protected static function returnTrue() { return true; }

      #[@test, @action(new \unittest\actions\VerifyThat("self::returnTrue"))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_static_method_on_this_returning_false() {
    $this->assertSkipped(['self::returnFalse'], newinstance('unittest.TestCase', ['fixture'], '{
      public static function returnFalse() { return false; }

      #[@test, @action(new \unittest\actions\VerifyThat("self::returnFalse"))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function with_static_method_on_other_class_returning_true() {
    $this->assertSucceeds(newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat("net.xp_framework.unittest.tests.VerifyThatTest::returnTrue"))]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function with_non_existant_method_on_this() {
    $this->assertSkipped(['$this->non_existant_method'], newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat("non_existant_method"))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function with_non_existant_method_on_self() {
    $this->assertSkipped(['self::non_existant_method'], newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat("self::non_existant_method"))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function with_non_existant_method_on_class() {
    $this->assertSkipped(['net.xp_framework.unittest.tests.VerifyThatTest::non_existant_method'], newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat("net.xp_framework.unittest.tests.VerifyThatTest::non_existant_method"))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function with_non_existant_class() {
    $this->assertSkipped(['non.existant.Class::irrelevant'], newinstance('unittest.TestCase', ['fixture'], '{
      #[@test, @action(new \unittest\actions\VerifyThat("non.existant.Class::irrelevant"))]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function on_class_returning_true() {
    $this->assertSucceeds(newinstance('#[@action(new \unittest\actions\VerifyThat("self::returnTrue"))] unittest.TestCase', ['fixture'], '{
      protected static function returnTrue() { return true; }

      #[@test]
      public function fixture() { }
    }'));
  }

  #[@test]
  public function on_class_returning_false() {
    $this->assertSkipped(['self::returnFalse'], newinstance('#[@action(new \unittest\actions\VerifyThat("self::returnFalse"))] unittest.TestCase', ['fixture'], '{
      protected static function returnFalse() { return false; }

      #[@test]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }

  #[@test]
  public function on_class_with_instance_method() {
    $this->assertSkipped(['$this->returnFalse'], newinstance('#[@action(new \unittest\actions\VerifyThat("returnFalse"))] unittest.TestCase', ['fixture'], '{
      protected function returnFalse() { return false; }

      #[@test]
      public function fixture() {
        throw new \lang\IllegalStateException("Should not be reached");
      }
    }'));
  }
}
