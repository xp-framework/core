<?php namespace net\xp_framework\unittest\tests\mock;

use unittest\mock\arguments\TypeMatcher;
use unittest\mock\MockRepository;
use lang\Type;
use lang\Object;
use util\Date;

/**
 * Testcase for TypeMatcher class
 *
 * @see   xp://unittest.mock.arguments.TypeMatcher
 */
class TypeMatcherTest extends \unittest\TestCase {

  #[@test]
  public function canCreate() {
    new TypeMatcher('test');
  }
  
  #[@test]
  public function object_matches_object() {
    $this->assertTrue((new TypeMatcher('lang.Object'))->matches(new Object()));
  }

  #[@test]
  public function object_does_not_match_string() {
    $this->assertFalse((new TypeMatcher('lang.Object'))->matches('a string'));
  }

  #[@test]
  public function object_does_not_match_subtype() {
    $this->assertFalse((new TypeMatcher('lang.Object'))->matches(new Date()));
  }

  #[@test]
  public function matches_should_not_match_parenttype() {
    $this->assertFalse((new TypeMatcher('unittest.mock.arguments.TypeMatcher'))->matches(new Object()));
  }
  
  #[@test]
  public function object_matches_null() {
    $this->assertTrue((new TypeMatcher('lang.Object'))->matches(null));
  }
  
  #[@test]
  public function matches_should_not_match_null_if_defined_so() {
    $this->assertFalse((new TypeMatcher('lang.Object', false))->matches(null));
  }
  
  #[@test]
  public function mock_repository_should_work_with() {
    $mockery= new MockRepository();
    $interface= $mockery->createMock('net.xp_framework.unittest.tests.mock.IComplexInterface');
    $interface->fooWithTypeHint(\unittest\mock\arguments\Arg::anyOfType('net.xp_framework.unittest.tests.mock.IEmptyInterface'));
  }
}
