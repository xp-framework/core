<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use util\collections\HashTable;

/**
 * Test the XP reflection API
 *
 * @see   xp://lang.reflect.Parameter
 */
class ParameterTest extends TestCase {

  /**
   * Method without functionality to be used by tests.
   *
   */
  private function initialize() { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   string name
   */
  private function setName($name) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   util.Date date default NULL
   */
  private function setDate($date= null) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   string format
   * @param   string* values
   */
  private function printf($format, $values= null) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   * value
   * @param   util.collections.Map context
   */
  private function serialize($value, \util\collections\Map $context) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   util.collections.HashTable map
   */
  private function setHashTable(HashTable $map) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   [:var] map
   */
  private function setHash(array $map) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   string[] map default array
   */
  private function setArray(array $map= array()) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   int a
   * @param   int b default 1
   */
  private function inc($a, $b= 1) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   bool new
   */
  private function setStatus($new= false) { }

  /**
   * Method without functionality to be used by tests.
   *
   * @param   rdbms.DBConnection conn
   * @param   string mode
   */
  #[@$conn: inject(name= "db")]
  private function setConnection($conn, $mode) { }

  #[@test]
  public function numParameters() {
    $this->assertEquals(0, $this->getClass()->getMethod('initialize')->numParameters(), 'initialize');
    $this->assertEquals(1, $this->getClass()->getMethod('setName')->numParameters(), 'setName');
    $this->assertEquals(1, $this->getClass()->getMethod('setDate')->numParameters(), 'setDate');
    $this->assertEquals(2, $this->getClass()->getMethod('printf')->numParameters(), 'printf');
    $this->assertEquals(2, $this->getClass()->getMethod('serialize')->numParameters(), 'serialize');
  }

  #[@test]
  public function getExistingParameter() {
    $this->assertInstanceOf('lang.reflect.Parameter', $this->getClass()->getMethod('setName')->getParameter(0));
  }

  #[@test]
  public function getNonExistantParameter() {
    $this->assertNull($this->getClass()->getMethod('initialize')->getParameter(0));
  }

  #[@test]
  public function initializeParameters() {
    $this->assertEquals(array(), $this->getClass()->getMethod('initialize')->getParameters());
  }

  #[@test]
  public function setNameParameters() {
    $params= $this->getClass()->getMethod('setName')->getParameters();
    $this->assertInstanceOf('lang.reflect.Parameter[]', $params);
    $this->assertEquals(1, sizeof($params));
  }

  #[@test]
  public function serializeParameters() {
    $params= $this->getClass()->getMethod('serialize')->getParameters();
    $this->assertInstanceOf('lang.reflect.Parameter[]', $params);
    $this->assertEquals(2, sizeof($params));
  }

  /**
   * Helper method to retrieve a method's parameter by its offset
   *
   * @param   string name
   * @param   string offset
   * @return  lang.reflect.Parameter
   */
  protected function methodParameter($name, $offset) {
    return $this->getClass()->getMethod($name)->getParameter($offset);
  }

  #[@test]
  public function name() {
    $this->assertEquals('name', $this->methodParameter('setName', 0)->getName(), 'setName#0');
    $this->assertEquals('date', $this->methodParameter('setDate', 0)->getName(), 'setDate#0');
    $this->assertEquals('value', $this->methodParameter('serialize', 0)->getName(), 'serialize#0');
    $this->assertEquals('context', $this->methodParameter('serialize', 1)->getName(), 'serialize#1');
  }

  #[@test]
  public function stringType() {
    $this->assertEquals(\lang\Primitive::$STRING, $this->methodParameter('setName', 0)->getType());
  }

  #[@test]
  public function intType() {
    $this->assertEquals(\lang\Primitive::$INT, $this->methodParameter('inc', 0)->getType(), 'inc$a');
    $this->assertEquals(\lang\Primitive::$INT, $this->methodParameter('inc', 1)->getType(), 'inc$b');
  }

  #[@test]
  public function booleanType() {
    $this->assertEquals(\lang\Primitive::$BOOL, $this->methodParameter('setStatus', 0)->getType());
  }

  #[@test]
  public function anyType() {
    $this->assertEquals(\lang\Type::$VAR, $this->methodParameter('serialize', 0)->getType());
  }

  #[@test]
  public function classType() {
    $this->assertEquals(\lang\XPClass::forName('util.Date'), $this->methodParameter('setDate', 0)->getType());
  }

  #[@test]
  public function interfaceType() {
    $this->assertEquals(\lang\XPClass::forName('util.collections.Map'), $this->methodParameter('serialize', 1)->getType());
  }

  #[@test]
  public function arrayType() {
    $this->assertEquals(\lang\ArrayType::forName('string[]'), $this->methodParameter('setArray', 0)->getType());
  }

  #[@test]
  public function varArgsArrayType() {
    $this->assertEquals(\lang\ArrayType::forName('string[]'), $this->methodParameter('printf', 1)->getType());
  }

  #[@test]
  public function typeRestriction() {
    $this->assertNull($this->methodParameter('setName', 0)->getTypeRestriction());
  }

  #[@test]
  public function isOptional() {
    $this->assertFalse($this->methodParameter('setName', 0)->isOptional());
    $this->assertTrue($this->methodParameter('setDate', 0)->isOptional());
  }

  #[@test]
  public function nullDefaultValue() {
    $this->assertNull($this->methodParameter('setDate', 0)->getDefaultValue());
  }

  #[@test]
  public function intDefaultValue() {
    $this->assertEquals(1, $this->methodParameter('inc', 1)->getDefaultValue());
  }

  #[@test]
  public function booleanDefaultValue() {
    $this->assertEquals(false, $this->methodParameter('setStatus', 0)->getDefaultValue());
  }

  #[@test]
  public function arrayDefaultValue() {
    $this->assertEquals(array(), $this->methodParameter('setArray', 0)->getDefaultValue());
  }

  #[@test]
  public function stringOfOptional() {
    $this->assertEquals(
      'lang.reflect.Parameter<lang.Primitive<bool> new= false>', 
      $this->methodParameter('setStatus', 0)->toString()
    );
  }

  #[@test]
  public function stringOfAnyTyped() {
    $this->assertEquals(
      'lang.reflect.Parameter<lang.Type<var> value>', 
      $this->methodParameter('serialize', 0)->toString()
    );
  }

  #[@test]
  public function stringOfClassTyped() {
    $this->assertEquals(
      'lang.reflect.Parameter<lang.XPClass<util.collections.Map> context>', 
      $this->methodParameter('serialize', 1)->toString()
    );
  }

  #[@test, @expect('lang.IllegalStateException')]
  public function defaultValueOfNonOptional() {
    $this->methodParameter('setName', 0)->getDefaultValue();
  }

  #[@test]
  public function unRestrictedParamType() {
    $this->assertNull($this->methodParameter('setDate', 0)->getTypeRestriction());
  }

  #[@test]
  public function restrictedParamClassType() {
    $this->assertEquals(
      \lang\XPClass::forName('util.collections.HashTable'),
      $this->methodParameter('setHashTable', 0)->getTypeRestriction()
    );
  }

  #[@test]
  public function restrictedParamArrayType() {
    $this->assertEquals(
      \lang\Primitive::$ARRAY,
      $this->methodParameter('setHash', 0)->getTypeRestriction(),
      'setHash'
    );
    $this->assertEquals(
      \lang\Primitive::$ARRAY,
      $this->methodParameter('setArray', 0)->getTypeRestriction(),
      'setArray'
    );
  }

  #[@test]
  public function annotatedParameterHasInjectAnnotation() {
    $this->assertTrue($this->methodParameter('setConnection', 0)->hasAnnotation('inject'));
  }

  #[@test]
  public function annotatedParametersInjectAnnotation() {
    $this->assertEquals(
      array('name' => 'db'), 
      $this->methodParameter('setConnection', 0)->getAnnotation('inject')
    );
  }

  #[@test]
  public function annotatedParameterHasAnnotations() {
    $this->assertTrue($this->methodParameter('setConnection', 0)->hasAnnotations());
  }

  #[@test]
  public function annotatedParametersAnnotations() {
    $this->assertEquals(
      array('inject' => array('name' => 'db')), 
      $this->methodParameter('setConnection', 0)->getAnnotations()
    );
  }

  #[@test]
  public function normalParameterHasNoAnnotations() {
    $this->assertFalse($this->methodParameter('setConnection', 1)->hasAnnotations());
  }

  #[@test]
  public function normalParametersAnnotations() {
    $this->assertEquals(
      array(), 
      $this->methodParameter('setConnection', 1)->getAnnotations()
    );
  }

  #[@test]
  public function normalParameterHasNoAnnotation() {
    $this->assertFalse($this->methodParameter('setConnection', 1)->hasAnnotation('irrelevant'));
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function normalParameterGetAnnotationRaisesException() {
    $this->methodParameter('setConnection', 1)->getAnnotation('irrelevant');
  }

  #[@test]
  public function inheritedConstructorsParameter() {
    $this->assertEquals(
      \lang\Primitive::$STRING,
      $this->getClass()->getConstructor()->getParameter(0)->getType()
    );
  }

  #[@test]
  public function inheritedConstructorsParameters() {
    $this->assertEquals(
      \lang\Primitive::$STRING,
      this($this->getClass()->getConstructor()->getParameters(), 0)->getType()
    );
  }
}
