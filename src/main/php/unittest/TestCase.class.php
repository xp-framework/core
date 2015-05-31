<?php namespace unittest;

use util\Objects;

/**
 * Test case is the base class for all unittests
 *
 * @see   php://assert
 */
class TestCase extends \lang\Object {
  public $name= '';
    
  /**
   * Constructor
   *
   * @param   string name
   */
  public function __construct($name) {
    $this->name= $name;
  }

  /**
   * Get this test cases' name
   *
   * @param   bool compound whether to use compound format
   * @return  string
   */
  public function getName($compound= false) {
    return $compound ? nameof($this).'::'.$this->name : $this->name;
  }

  /**
   * Fail this test case
   *
   * @param   string reason
   * @param   var actual
   * @param   var expect
   * @return  void
   */
  public function fail($reason, $actual, $expect) {
    throw new AssertionFailedError(new ComparisonFailedMessage($reason, $expect, $actual));
  }

  /**
   * Skip this test case
   *
   * @param   string reason
   * @param   var[] prerequisites default []
   * @return  void
   */
  public function skip($reason, $prerequisites= []) {
    throw new PrerequisitesNotMetError($reason, null, $prerequisites= []);
  }

  /**
   * Assert that two values are equal
   *
   * @param   var expected
   * @param   var actual
   * @param   string error default 'notequal'
   * @return  void
   */
  public function assertEquals($expected, $actual, $error= 'equals') {
    if (!Objects::equal($expected, $actual)) {
      $this->fail($error, $actual, $expected);
    }
  }
  
  /**
   * Assert that two values are not equal
   *
   * @param   var expected
   * @param   var actual
   * @param   string error default 'equal'
   * @return  void
   */
  public function assertNotEquals($expected, $actual, $error= '!equals') {
    if (Objects::equal($expected, $actual)) {
      $this->fail($error, $actual, $expected);
    }
  }

  /**
   * Assert that a value is true
   *
   * @param   var var
   * @param   string error default '==='
   * @return  void
   */
  public function assertTrue($var, $error= '===') {
    if (true !== $var) {
      $this->fail($error, $var, true);
    }
  }
  
  /**
   * Assert that a value is false
   *
   * @param   var var
   * @param   string error default '==='
   * @return  void
   */
  public function assertFalse($var, $error= '===') {
    if (false !== $var) {
      $this->fail($error, $var, false);
    }
  }

  /**
   * Assert that a value's type is null
   *
   * @param   var var
   * @param   string error default '==='
   * @return  void
   */
  public function assertNull($var, $error= '===') {
    if (null !== $var) {
      $this->fail($error, $var, null);
    }
  }

  /**
   * Assert that a given object is a subclass of a specified class
   *
   * @param   var type either a type name or a lang.Type instance
   * @param   var var
   * @param   string error default 'instanceof'
   * @return  void
   */
  public function assertInstanceOf($type, $var, $error= 'instanceof') {
    if (!($type instanceof \lang\Type)) {
      $type= \lang\Type::forName($type);
    }
    
    $type->isInstance($var) || $this->fail($error, \xp::typeOf($var), $type->getName());
  }
  
  /**
   * Set up this test. Overwrite in subclasses. Throw a 
   * PrerequisitesNotMetError to indicate this case should be
   * skipped.
   *
   * @return  void
   * @throws  unittest.PrerequisitesNotMetError
   */
  public function setUp() { }
  
  /**
   * Tear down this test case. Overwrite in subclasses.
   *
   * @return  void
   */
  public function tearDown() { }
  
  /**
   * Creates a string representation of this testcase
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'<'.$this->name.'>';
  }

  /**
   * Returns whether an object is equal to this testcase
   *
   * @param   var $cmp
   * @return  bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $this->name == $cmp->name;
  }
}
