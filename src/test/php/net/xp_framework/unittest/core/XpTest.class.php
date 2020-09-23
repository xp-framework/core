<?php namespace net\xp_framework\unittest\core;

/**
 * Tests the <xp> functions
 */
class XpTest extends \unittest\TestCase {

  #[@test]
  public function version() {
    $this->assertEquals(3, sscanf(\xp::version(), '%d.%d.%d', $series, $major, $minor));
  }

  #[@test]
  public function no_error_here() {
    $this->assertNull(\xp::errorAt(__FILE__));
  }

  #[@test]
  public function triggered_error_at_file() {
    trigger_error('Test');
    $this->assertEquals(
      [__LINE__ - 2 => ['Test' => ['class' => NULL, 'method' => 'trigger_error', 'cnt' => 1]]],
      \xp::errorAt(__FILE__)
    );
    \xp::gc();
  }

  #[@test]
  public function triggered_error_at_file_and_line() {
    trigger_error('Test');
    $this->assertEquals(
      ['Test' => ['class' => NULL, 'method' => 'trigger_error', 'cnt' => 1]],
      \xp::errorAt(__FILE__, __LINE__ - 3)
    );
    \xp::gc();
  }

  #[@test]
  public function gc() {
    trigger_error('Test');
    $this->assertEquals(
      [__FILE__ => [__LINE__ - 2 => ['Test' => ['class' => NULL, 'method' => 'trigger_error', 'cnt' => 1]]]],
      \xp::$errors
    );
    \xp::gc();
    $this->assertEquals([], \xp::$errors);
  }

  #[@test]
  public function literal_of_int() {
    $this->assertEquals("\xfeint", literal('int'));
  }

  #[@test]
  public function literal_of_float() {
    $this->assertEquals("\xfefloat", literal('float'));
  }

  #[@test]
  public function literal_of_float_alias_double() {
    $this->assertEquals("\xfefloat", literal('double'));
  }

  #[@test]
  public function literal_of_string() {
    $this->assertEquals("\xfestring", literal('string'));
  }

  #[@test]
  public function literal_of_bool() {
    $this->assertEquals("\xfebool", literal('bool'));
  }

  #[@test]
  public function literal_of_var() {
    $this->assertEquals('var', literal('var'));
  }

  #[@test]
  public function literal_of_int_array() {
    $this->assertEquals("\xa6\xfeint", literal('int[]'));
  }

  #[@test]
  public function literal_of_int_map() {
    $this->assertEquals("\xbb\xfeint", literal('[:int]'));
  }

  #[@test]
  public function literal_of_generic_list_of_int() {
    $this->assertEquals("List\xb7\xb7\xfeint", literal('List<int>'));
  }

  #[@test]
  public function literal_of_value() {
    $this->assertEquals(Value::class, literal('net.xp_framework.unittest.core.Value'));
  }

  #[@test]
  public function literal_of_this() {
    $this->assertEquals(self::class, literal(nameof($this)));
  }
}
