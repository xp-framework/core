<?php namespace net\xp_framework\unittest\core;

use unittest\{Test, TestCase};

class XpTest extends TestCase {

  #[Test]
  public function version() {
    $this->assertEquals(3, sscanf(\xp::version(), '%d.%d.%d', $series, $major, $minor));
  }

  #[Test]
  public function gc() {
    trigger_error('Test');
    $this->assertEquals(
      [__FILE__ => [__LINE__ - 2 => ['Test' => ['class' => NULL, 'method' => 'trigger_error', 'cnt' => 1]]]],
      \xp::$errors
    );
    \xp::gc();
    $this->assertEquals([], \xp::$errors);
  }

  #[Test]
  public function literal_of_int() {
    $this->assertEquals("\xfeint", literal('int'));
  }

  #[Test]
  public function literal_of_float() {
    $this->assertEquals("\xfefloat", literal('float'));
  }

  #[Test]
  public function literal_of_float_alias_double() {
    $this->assertEquals("\xfefloat", literal('double'));
  }

  #[Test]
  public function literal_of_string() {
    $this->assertEquals("\xfestring", literal('string'));
  }

  #[Test]
  public function literal_of_bool() {
    $this->assertEquals("\xfebool", literal('bool'));
  }

  #[Test]
  public function literal_of_var() {
    $this->assertEquals('var', literal('var'));
  }

  #[Test]
  public function literal_of_int_array() {
    $this->assertEquals("\xa6\xfeint", literal('int[]'));
  }

  #[Test]
  public function literal_of_int_map() {
    $this->assertEquals("\xbb\xfeint", literal('[:int]'));
  }

  #[Test]
  public function literal_of_generic_list_of_int() {
    $this->assertEquals("List\xb7\xb7\xfeint", literal('List<int>'));
  }

  #[Test]
  public function literal_of_value() {
    $this->assertEquals(Value::class, literal('net.xp_framework.unittest.core.Value'));
  }

  #[Test]
  public function literal_of_this() {
    $this->assertEquals(self::class, literal(nameof($this)));
  }
}