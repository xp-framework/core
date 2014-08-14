<?php namespace net\xp_framework\unittest\tests\mock;

use unittest\mock\arguments\PatternMatcher;

/**
 * Testcase for PatternMatcher class
 *
 * @see   xp://unittest.mock.arguments.PatternMatcher
 */
class PatternMatcherTest extends \unittest\TestCase {

  #[@test]
  public function construction_should_work_with_string_parameter() {
    new PatternMatcher('foobar');
  }

  #[@test, @values(['foooo', 'foo', 'foo ', 'foo asdfa'])]
  public function prefix_match_test_matches($value) {
    $this->assertTrue((new PatternMatcher('/^foo/'))->matches($value));
  }

  #[@test, @values(['xfoo', ' foo '])]
  public function prefix_match_test_does_not_match($value) {
    $this->assertFalse((new PatternMatcher('/^foo/'))->matches($value));
  }

  #[@test]
  public function exact_match_test() {
    $this->assertTrue((new PatternMatcher('/^foo$/'))->matches('foo'));
  }

  #[@test, @values(['foooo', 'foo ', 'foo asdfa', 'xfoox', ' foo '])]
  public function exact_match_negative_tests($value) {
    $this->assertFalse((new PatternMatcher('/^foo$/'))->matches($value));
  }

  #[@test, @values(['foooo', 'fooooooooo', 'adsfafdsfooooooooo', 'asdfaf fooo dsfasfd'])]
  public function pattern_match_test($value) {
    $this->assertTrue((new PatternMatcher('/fo+o.*/'))->matches($value));
  }

  #[@test, @values(['fobo', 'fo'])]
  public function pattern_match_test_negative_tests($value) {
    $this->assertFalse((new PatternMatcher('/fo+o.*/'))->matches($value));
  }
}
