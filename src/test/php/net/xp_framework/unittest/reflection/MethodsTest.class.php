<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Type;
use lang\MapType;
use lang\Primitive;

/**
 * TestCase
 *
 * @see    xp://lang.reflect.Method
 */
class MethodsTest extends \unittest\TestCase {
  protected $fixture;

  /**
   * Sets up test case
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
  }
  
  /**
   * Assertion helper
   *
   * @param   lang.Generic var
   * @param   lang.Generic[] list
   * @throws  unittest.AssertionFailedError
   */
  protected function assertNotContained($var, $list) {
    foreach ($list as $i => $element) {
      if ($element->equals($var)) $this->fail('Element contained', 'Found at offset '.$i, null);
    }
  }

  /**
   * Assertion helper
   *
   * @param   lang.Generic var
   * @param   lang.Generic[] list
   * @throws  unittest.AssertionFailedError
   */
  protected function assertContained($var, $list) {
    foreach ($list as $i => $element) {
      if ($element->equals($var)) return;
    }
    $this->fail('Element not contained in list', null, $var);
  }

  /**
   * Helper method
   *
   * @param   int modifiers
   * @param   string method
   * @throws  unittest.AssertionFailedError
   */
  protected function assertModifiers($modifiers, $method) {
    $this->assertEquals($modifiers, $this->fixture->getMethod($method)->getModifiers());
  }

  #[@test]
  public function methods() {
    $methods= $this->fixture->getMethods();
    $this->assertInstanceOf('lang.reflect.Method[]', $methods);
    $this->assertContained($this->fixture->getMethod('equals'), $methods);
  }

  #[@test]
  public function declaredMethods() {
    $methods= $this->fixture->getDeclaredMethods();
    $this->assertInstanceOf('lang.reflect.Method[]', $methods);
    $this->assertNotContained($this->fixture->getMethod('hashCode'), $methods);
  }
  
  #[@test]
  public function declaredMethod() {
    $this->assertEquals(
      $this->fixture,
      $this->fixture->getMethod('setDate')->getDeclaringClass()
    );
  }

  #[@test]
  public function inheritedMethod() {
    $this->assertEquals(
      $this->fixture->getParentClass(),
      $this->fixture->getMethod('clearDate')->getDeclaringClass()
    );
  }

  #[@test]
  public function nonExistantMethod() {
    $this->assertFalse($this->fixture->hasMethod('@@nonexistant@@'));
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function getNonExistantMethod() {
    $this->fixture->getMethod('@@nonexistant@@');
  }

  #[@test]
  public function checkConstructorIsNotAMethod() {
    $this->assertFalse($this->fixture->hasMethod('__construct'));
  }
  
  #[@test, @expect('lang.ElementNotFoundException')]
  public function constructorIsNotAMethod() {
    $this->fixture->getMethod('__construct');
  }

  #[@test]
  public function checkStaticInitializerIsNotAMethod() {
    $this->assertFalse($this->fixture->hasMethod('__static'));
  }
  
  #[@test, @expect('lang.ElementNotFoundException')]
  public function staticInitializerIsNotAMethod() {
    $this->fixture->getMethod('__static');
  }

  #[@test]
  public function publicMethod() {
    $this->assertModifiers(MODIFIER_PUBLIC, 'getMap');
  }

  #[@test]
  public function privateMethod() {
    $this->assertModifiers(MODIFIER_PRIVATE, 'defaultMap');
  }

  #[@test]
  public function protectedMethod() {
    $this->assertModifiers(MODIFIER_PROTECTED, 'clearMap');
  }

  #[@test]
  public function finalMethod() {
    $this->assertModifiers(MODIFIER_FINAL | MODIFIER_PUBLIC, 'setMap');
  }

  #[@test]
  public function staticMethod() {
    $this->assertModifiers(MODIFIER_STATIC | MODIFIER_PUBLIC, 'fromMap');
  }

  #[@test]
  public function abstractMethod() {
  
    // AbstractTestClass declares the method abstract (and therefore does not
    // implement it)
    $this->assertEquals(
      MODIFIER_PUBLIC | MODIFIER_ABSTRACT, 
      $this->fixture->getParentClass()->getMethod('getDate')->getModifiers()
    );

    // TestClass implements the method
    $this->assertModifiers(
      MODIFIER_PUBLIC, 
      'getDate'
    );
  }
  
  #[@test]
  public function getDateMethod() {
    $this->assertTrue($this->fixture->hasMethod('getDate'));
    with ($method= $this->fixture->getMethod('getDate')); {
      $this->assertInstanceOf('lang.reflect.Method', $method);
      $this->assertEquals('getDate', $method->getName(true));
      $this->assertTrue($this->fixture->equals($method->getDeclaringClass()));
      $this->assertEquals('util.Date', $method->getReturnTypeName());
    }
  }

  #[@test, @expect('lang.reflect.TargetInvocationException')]
  public function invokeSetTrace() {
    $this->fixture->getMethod('setTrace')->invoke($this->fixture->newInstance(), array(null));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function invokeSetTraceOnWrongObject() {
    $this->fixture->getMethod('setTrace')->invoke(new \lang\Object(), array(null));
  }

  #[@test]
  public function invokeStaticMethod() {
    $this->assertTrue($this->fixture->getMethod('initializerCalled')->invoke(null));
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function invokePrivateMethod() {
    $this->fixture->getMethod('defaultMap')->invoke($this->fixture->newInstance());
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function invokeProtectedMethod() {
    $this->fixture->getMethod('clearMap')->invoke($this->fixture->newInstance());
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function invokeAbstractMethod() {
    XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')
      ->getMethod('getDate')
      ->invoke($this->fixture->newInstance())
    ;
  }

  #[@test]
  public function invokeMethodWithoutReturn() {
    $i= $this->fixture->newInstance();
    $d= new \util\Date();
    $this->assertNull($this->fixture->getMethod('setDate')->invoke($i, array($d)));
    $this->assertEquals($d, $i->getDate());
  }

  #[@test]
  public function voidReturnValue() {
    $this->assertEquals('void', $this->fixture->getMethod('setDate')->getReturnTypeName());
    $this->assertEquals(Type::$VOID, $this->fixture->getMethod('setDate')->getReturnType());
  }

  #[@test]
  public function selfReturnValue() {
    $this->assertEquals('self', $this->fixture->getMethod('withDate')->getReturnTypeName());
    $this->assertEquals($this->fixture, $this->fixture->getMethod('withDate')->getReturnType());
  }

  #[@test]
  public function boolReturnValue() {
    $this->assertEquals('bool', $this->fixture->getMethod('initializerCalled')->getReturnTypeName());
    $this->assertEquals(Primitive::$BOOL, $this->fixture->getMethod('initializerCalled')->getReturnType());
  }
  
  #[@test]
  public function genericReturnValue() {
    $this->assertEquals('[:lang.Object]', $this->fixture->getMethod('getMap')->getReturnTypeName());
    $this->assertEquals(MapType::forName('[:lang.Object]'), $this->fixture->getMethod('getMap')->getReturnType());
  }

  #[@test]
  public function getMapString() {
    $this->assertEquals(
      'public [:lang.Object] getMap()', 
      $this->fixture->getMethod('getMap')->toString()
    );
  }

  #[@test]
  public function filterMapString() {
    $this->assertEquals(
      'public util.collections.Vector<lang.Object> filterMap([string $pattern= null])',
      $this->fixture->getMethod('filterMap')->toString()
    );
  }

  #[@test]
  public function getDateString() {
    $this->assertEquals(
      'public util.Date getDate()', 
      $this->fixture->getMethod('getDate')->toString()
    );
  }

  #[@test]
  public function clearMapString() {
    $this->assertEquals(
      'protected var clearMap()', 
      $this->fixture->getMethod('clearMap')->toString()
    );
  }

  #[@test]
  public function fromMapString() {
    $this->assertEquals(
      'public static net.xp_framework.unittest.reflection.TestClass fromMap([:lang.Object] $map)', 
      $this->fixture->getMethod('fromMap')->toString()
    );
  }

  #[@test]
  public function setTraceString() {
    $this->assertEquals(
      'public void setTrace(util.log.LogCategory $cat) throws lang.IllegalStateException', 
      $this->fixture->getMethod('setTrace')->toString()
    );
  }

  #[@test]
  public function thrownExceptionNames() {
    $this->assertEquals(
      ['lang.IllegalArgumentException', 'lang.IllegalStateException'],
      $this->fixture->getMethod('setDate')->getExceptionNames(),
      'with multiple throws'
    );
    $this->assertEquals(
      ['lang.IllegalStateException'],
      $this->fixture->getMethod('setTrace')->getExceptionNames(),
      'with throws'
    );
    $this->assertEquals(
      [], 
      $this->fixture->getMethod('currentTimestamp')->getExceptionNames(),
      'without throws'
    );
  }

  #[@test]
  public function thrownExceptionTypes() {
    $this->assertEquals(
      [XPClass::forName('lang.IllegalArgumentException'), XPClass::forName('lang.IllegalStateException')],
      $this->fixture->getMethod('setDate')->getExceptionTypes(),
      'with multiple throws'
    );
    $this->assertEquals(
      [XPClass::forName('lang.IllegalStateException')],
      $this->fixture->getMethod('setTrace')->getExceptionTypes(),
      'with throws'
    );
    $this->assertEquals(
      [], 
      $this->fixture->getMethod('currentTimestamp')->getExceptionTypes(),
      'without throws'
    );
  }

  #[@test]
  public function equality() {
    $this->assertEquals(
      $this->fixture->getMethod('setTrace'),
      $this->fixture->getMethod('setTrace')
    );
  }

  #[@test]
  public function notEqualToNull() {
    $this->assertFalse($this->fixture->getMethod('setTrace')->equals(null));
  }

  #[@test]
  public function inheritedMethodsAreNotEqual() {
    $this->assertNotEquals(
      $this->fixture->getMethod('getDate'),
      $this->fixture->getParentClass()->getMethod('getDate')
    );
  }

  #[@test]
  public function methodDetailsForInheritedInterfaceMethod() {
    $this->assertEquals(
      'io.collections.IOCollection', 
      XPClass::forName('io.collections.IOCollection')->getMethod('getOrigin')->getReturnTypeName()
    );
  }

  #[@test]
  public function arrayAccessMethod() {
    if (defined('HHVM_VERSION')) {
      $expected= 'public abstract var offsetGet(var $index)';
    } else {
      $expected= 'public abstract var offsetGet(var $offset)';
    }

    $this->assertEquals(
      $expected,
      XPClass::forName('util.collections.Map')->getMethod('offsetGet')->toString()
    );
  }

  #[@test]
  public function notDocumentedReturnType() {
    $this->assertEquals('var', $this->fixture->getMethod('notDocumented')->getReturnTypeName());
    $this->assertEquals(Type::$VAR, $this->fixture->getMethod('notDocumented')->getReturnType());
  }

  #[@test]
  public function notDocumentedParameterType() {
    $this->assertEquals('var', $this->fixture->getMethod('notDocumented')->getParameter(0)->getTypeName());
    $this->assertEquals(Type::$VAR, $this->fixture->getMethod('notDocumented')->getParameter(0)->getType());
  }
}
