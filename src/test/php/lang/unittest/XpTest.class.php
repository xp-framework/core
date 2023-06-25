<?php namespace lang\unittest;

use unittest\{Assert, Test};

class XpTest {

  #[Test]
  public function version() {
    Assert::equals(3, sscanf(\xp::version(), '%d.%d.%d', $series, $major, $minor));
  }

  #[Test]
  public function gc() {
    trigger_error('Test');
    Assert::equals(
      [__FILE__ => [__LINE__ - 2 => ['Test' => ['class' => NULL, 'method' => 'trigger_error', 'cnt' => 1]]]],
      \xp::$errors
    );
    \xp::gc();
    Assert::equals([], \xp::$errors);
  }

  #[Test]
  public function literal_of_int() {
    Assert::equals("\xfeint", literal('int'));
  }

  #[Test]
  public function literal_of_float() {
    Assert::equals("\xfefloat", literal('float'));
  }

  #[Test]
  public function literal_of_float_alias_double() {
    Assert::equals("\xfefloat", literal('double'));
  }

  #[Test]
  public function literal_of_string() {
    Assert::equals("\xfestring", literal('string'));
  }

  #[Test]
  public function literal_of_bool() {
    Assert::equals("\xfebool", literal('bool'));
  }

  #[Test]
  public function literal_of_var() {
    Assert::equals('var', literal('var'));
  }

  #[Test]
  public function literal_of_int_array() {
    Assert::equals("\xa6\xfeint", literal('int[]'));
  }

  #[Test]
  public function literal_of_int_map() {
    Assert::equals("\xbb\xfeint", literal('[:int]'));
  }

  #[Test]
  public function literal_of_generic_list_of_int() {
    Assert::equals("List\xb7\xb7\xfeint", literal('List<int>'));
  }

  #[Test]
  public function literal_of_value() {
    Assert::equals(Value::class, literal('lang.unittest.Value'));
  }

  #[Test]
  public function literal_of_this() {
    Assert::equals(self::class, literal(nameof($this)));
  }
}