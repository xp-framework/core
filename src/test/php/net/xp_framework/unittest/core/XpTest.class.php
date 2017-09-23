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
    $this->assertEquals('þint', literal('int'));
  }

  #[@test]
  public function literal_of_double() {
    $this->assertEquals('þdouble', literal('double'));
  }

  #[@test]
  public function literal_of_string() {
    $this->assertEquals('þstring', literal('string'));
  }

  #[@test]
  public function literal_of_bool() {
    $this->assertEquals('þbool', literal('bool'));
  }

  #[@test]
  public function literal_of_var() {
    $this->assertEquals('var', literal('var'));
  }

  #[@test]
  public function literal_of_int_array() {
    $this->assertEquals('¦þint', literal('int[]'));
  }

  #[@test]
  public function literal_of_int_map() {
    $this->assertEquals('»þint', literal('[:int]'));
  }

  #[@test]
  public function literal_of_generic_list_of_int() {
    $this->assertEquals('List··þint', literal('List<int>'));
  }

  #[@test]
  public function literal_of_value() {
    $this->assertEquals(Value::class, literal('lang.Value'));
  }

  #[@test]
  public function literal_of_this() {
    $this->assertEquals(self::class, literal(nameof($this)));
  }
}
