<?php namespace net\xp_framework\unittest\core;

/**
 * Tests the <xp> functions
 *
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
      array(__LINE__ - 2 => array('Test' => array('class' => NULL, 'method' => 'trigger_error', 'cnt' => 1))),
      \xp::errorAt(__FILE__)
    );
    \xp::gc();
  }

  #[@test]
  public function triggered_error_at_file_and_line() {
    trigger_error('Test');
    $this->assertEquals(
      array('Test' => array('class' => NULL, 'method' => 'trigger_error', 'cnt' => 1)),
      \xp::errorAt(__FILE__, __LINE__ - 3)
    );
    \xp::gc();
  }

  #[@test]
  public function gc() {
    trigger_error('Test');
    $this->assertEquals(
      array(__FILE__ => array(__LINE__ - 2 => array('Test' => array('class' => NULL, 'method' => 'trigger_error', 'cnt' => 1)))),
      \xp::$errors
    );
    \xp::gc();
    $this->assertEquals([], \xp::$errors);
  }

  /** @deprecated */
  #[@test]
  public function null() {
    $this->assertEquals('null', get_class(\xp::null()));
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
  public function literal_of_object() {
    $this->assertEquals('lang\Object', literal('lang.Object'));
  }

  #[@test]
  public function literal_of_this() {
    $this->assertEquals(__CLASS__, literal(nameof($this)));
  }
}
