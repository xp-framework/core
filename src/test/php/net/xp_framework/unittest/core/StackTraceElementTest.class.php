<?php namespace net\xp_framework\unittest\core;

use lang\StackTraceElement;
use net\xp_framework\unittest\Name;
use unittest\Test;

/**
 * Tests for the StackTraceElement class
 */
class StackTraceElementTest extends \unittest\TestCase {
  const NEW_FIXTURE_METHOD = '  at net.xp_framework.unittest.core.StackTraceElementTest::newFixtureWith';

  /**
   * Creates a new fixture with args set to the given arguments
   *
   * @param  var[] $args The arguments
   * @return lang.StackTraceElement the fixture
   */
  protected function newFixtureWith($args) {
    return new StackTraceElement('Test.class.php', self::class, __FUNCTION__, 1, $args, 'Test');
  }

  #[Test]
  public function can_create() {
    new StackTraceElement('file', 'class', 'method', 1, [], 'Message');
  }

  #[Test]
  public function is_equal_to_itself() {
    $a= new StackTraceElement('file', 'class', 'method', 1, [], 'Message');
    $this->assertEquals($a, $a);
  }

  #[Test]
  public function two_identical_stacktraceelements_are_equal() {
    $a= new StackTraceElement('file', 'class', 'method', 1, [], 'Message');
    $b= new StackTraceElement('file', 'class', 'method', 1, [], 'Message');
    $this->assertEquals($a, $b);
  }

  #[Test]
  public function to_string() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."() [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([])->toString()
    );
  }

  #[Test]
  public function to_string_with_array_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(array[3]) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([[1, 2, 3]])->toString()
    );
  }

  #[Test]
  public function to_string_with_empty_array_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(array[0]) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([[]])->toString()
    );
  }

  #[Test]
  public function to_string_with_string_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."((0x5)'Hello') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith(['Hello'])->toString()
    );
  }

  #[Test]
  public function to_string_with_long_string_arg() {
    $str= str_repeat('*', 0x80);
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."((0x80)'".str_repeat('*', 0x40)."') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([$str])->toString()
    );
  }

  #[Test]
  public function to_string_with_string_with_newline_arg() {
    $str= "Hello\nWorld";
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."((0xb)'Hello') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([$str])->toString()
    );
  }

  #[Test]
  public function to_string_with_string_with_nul_arg() {
    $str= "Hello\0";
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."((0x6)'Hello\\000') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([$str])->toString()
    );
  }

  #[Test]
  public function to_string_with_int_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(6100) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([6100])->toString()
    );
  }

  #[Test]
  public function to_string_with_double_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(-1.5) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([-1.5])->toString()
    );
  }

  #[Test]
  public function to_string_with_bool_true_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(1) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([true])->toString()
    );
  }

  #[Test]
  public function to_string_with_bool_false_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."() [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([false])->toString()
    );
  }

  #[Test]
  public function to_string_with_null_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(NULL) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([null])->toString()
    );
  }

  #[Test]
  public function to_string_with_object_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(net.xp_framework.unittest.Name{}) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([new Name('test')])->toString()
    );
  }

  #[Test]
  public function to_string_with_two_args() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."((0x5)'Hello', 2) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith(['Hello', 2])->toString()
    );
  }

  #[Test]
  public function to_string_with_resource_arg() {
    $fd= fopen(__FILE__, 'r');
    $string= $this->newFixtureWith([$fd])->toString();
    $fds= (string)$fd;
    fclose($fd);

    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(".$fds.") [line 1 of Test.class.php] Test\n",
      $string
    );
  }

  #[Test]
  public function to_string_with_function_arg() {
    $this->assertEquals(
      self::NEW_FIXTURE_METHOD."(function()) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([function() { }])->toString()
    );
  }
}