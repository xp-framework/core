<?php namespace net\xp_framework\unittest\tests;

/**
 * This class is used in the TestClassActionTest 
 */
#[@action(new RecordClassActionInvocation('run'))]
class TestWithClassAction extends \unittest\TestCase {
  public static $run= [];

  #[@test]
  public function fixture() {
    self::$run[]= 'test';
  }
}
