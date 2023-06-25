<?php namespace lang\unittest;

use lang\StackTraceElement;
use test\{Assert, Test};

class StackTraceElementTest {
  const NEW_FIXTURE_METHOD= '  at lang.unittest.StackTraceElementTest::newFixtureWith';

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
    Assert::equals($a, $a);
  }

  #[Test]
  public function two_identical_stacktraceelements_are_equal() {
    $a= new StackTraceElement('file', 'class', 'method', 1, [], 'Message');
    $b= new StackTraceElement('file', 'class', 'method', 1, [], 'Message');
    Assert::equals($a, $b);
  }

  #[Test]
  public function to_string() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."() [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([])->toString()
    );
  }

  #[Test]
  public function to_string_with_array_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(array[3]) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([[1, 2, 3]])->toString()
    );
  }

  #[Test]
  public function to_string_with_empty_array_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(array[0]) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([[]])->toString()
    );
  }

  #[Test]
  public function to_string_with_string_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."((0x5)'Hello') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith(['Hello'])->toString()
    );
  }

  #[Test]
  public function to_string_with_long_string_arg() {
    $str= str_repeat('*', 0x80);
    Assert::equals(
      self::NEW_FIXTURE_METHOD."((0x80)'".str_repeat('*', 0x40)."') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([$str])->toString()
    );
  }

  #[Test]
  public function to_string_with_string_with_newline_arg() {
    $str= "Hello\nWorld";
    Assert::equals(
      self::NEW_FIXTURE_METHOD."((0xb)'Hello') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([$str])->toString()
    );
  }

  #[Test]
  public function to_string_with_string_with_nul_arg() {
    $str= "Hello\0";
    Assert::equals(
      self::NEW_FIXTURE_METHOD."((0x6)'Hello\\000') [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([$str])->toString()
    );
  }

  #[Test]
  public function to_string_with_int_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(6100) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([6100])->toString()
    );
  }

  #[Test]
  public function to_string_with_double_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(-1.5) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([-1.5])->toString()
    );
  }

  #[Test]
  public function to_string_with_bool_true_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(1) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([true])->toString()
    );
  }

  #[Test]
  public function to_string_with_bool_false_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."() [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([false])->toString()
    );
  }

  #[Test]
  public function to_string_with_null_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(NULL) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([null])->toString()
    );
  }

  #[Test]
  public function to_string_with_object_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(lang.unittest.Name{}) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([new Name('test')])->toString()
    );
  }

  #[Test]
  public function to_string_with_two_args() {
    Assert::equals(
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

    Assert::equals(
      self::NEW_FIXTURE_METHOD."(".$fds.") [line 1 of Test.class.php] Test\n",
      $string
    );
  }

  #[Test]
  public function to_string_with_function_arg() {
    Assert::equals(
      self::NEW_FIXTURE_METHOD."(function()) [line 1 of Test.class.php] Test\n",
      $this->newFixtureWith([function() { }])->toString()
    );
  }
}