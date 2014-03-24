<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\Proxy;
use lang\XPClass;
use lang\ClassLoader;
use lang\reflect\InvocationHandler;
use util\XPIterator;
use util\Observer;

/**
 * Tests the Proxy class
 *
 * @see   xp://lang.reflect.Proxy
 */
class ProxyTest extends \unittest\TestCase {
  protected $handler       = null;
  protected $iteratorClass = null;
  protected $observerClass = null;

  /**
   * Setup method 
   */
  public function setUp() {
    $this->handler= newinstance('lang.reflect.InvocationHandler', [], array(
      'invocations' => [],
      'invoke' => function($proxy, $method, $args) {
        $this->invocations[$method.'_'.sizeof($args)]= $args;
      }
    ));
    $this->iteratorClass= XPClass::forName('util.XPIterator');
    $this->observerClass= XPClass::forName('util.Observer');
  }

  /**
   * Helper method which returns a proxy instance for a given list of
   * interfaces, using the default classloader and the handler defined
   * in setUp()
   *
   * @param   lang.XPClass[] interfaces
   * @return  lang.reflect.Proxy
   */
  protected function proxyInstanceFor($interfaces) {
    return Proxy::newProxyInstance(
      ClassLoader::getDefault(),
      $interfaces, 
      $this->handler
    );
  }
  
  /**
   * Helper method which returns a proxy class for a given list of
   * interfaces, using the default classloader and the handler defined
   * in setUp()
   *
   * @param   lang.XPClass[] interfaces
   * @return  lang.XPClass
   */
  protected function proxyClassFor($interfaces) {
    return Proxy::getProxyClass(
      ClassLoader::getDefault(),
      $interfaces,
      $this->handler
    );
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function nullClassLoader() {
    Proxy::getProxyClass(null, array($this->iteratorClass));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function emptyInterfaces() {
    Proxy::getProxyClass(ClassLoader::getDefault(), []);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function nullInterfaces() {
    Proxy::getProxyClass(ClassLoader::getDefault(), null);
  }

  #[@test]
  public function proxyClassNamesGetPrefixed() {
    $class= $this->proxyClassFor(array($this->iteratorClass));
    $this->assertEquals(Proxy::PREFIX, substr($class->getName(), 0, strlen(Proxy::PREFIX)));
  }

  #[@test]
  public function classesEqualForSameInterfaceList() {
    $c1= $this->proxyClassFor(array($this->iteratorClass));
    $c2= $this->proxyClassFor(array($this->iteratorClass));

    $this->assertEquals($c1, $c2);
  }

  #[@test]
  public function classesNotEqualForDifferingInterfaceList() {
    $c1= $this->proxyClassFor(array($this->iteratorClass));
    $c2= $this->proxyClassFor(array($this->iteratorClass, $this->observerClass));

    $this->assertNotEquals($c1, $c2);
  }

  #[@test]
  public function iteratorInterfaceIsImplemented() {
    $class= $this->proxyClassFor(array($this->iteratorClass));
    $interfaces= $class->getInterfaces();
    $this->assertEquals(2, sizeof($interfaces));
    $this->assertTrue(in_array($this->iteratorClass, $interfaces)); 
  }

  #[@test]
  public function allInterfacesAreImplemented() {
    $class= $this->proxyClassFor(array($this->iteratorClass, $this->observerClass));
    $interfaces= $class->getInterfaces();
    $this->assertEquals(3, sizeof($interfaces));
    $this->assertTrue(in_array($this->iteratorClass, $interfaces));
    $this->assertTrue(in_array($this->observerClass, $interfaces));
  }

  #[@test]
  public function iteratorMethods() {
    $expected= array(
      'hashcode', 'equals', 'getclassname', 'getclass', 'tostring', // lang.Object
      'getproxyclass', 'newproxyinstance',                          // lang.reflect.Proxy
      'hasnext', 'next'                                             // util.XPIterator
    );
    
    $class= $this->proxyClassFor(array($this->iteratorClass));
    $methods= $class->getMethods();

    $this->assertEquals(sizeof($expected), sizeof($methods));
    foreach ($methods as $method) {
      $this->assertTrue(
        in_array(strtolower($method->getName()), $expected), 
        'Unexpected method "'.$method->getName().'"'
      );
    }
  }

  #[@test]
  public function iteratorNextInvoked() {
    $proxy= $this->proxyInstanceFor(array($this->iteratorClass));
    $proxy->next();
    $this->assertEquals([], $this->handler->invocations['next_0']);
  }
  
  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannotCreateProxiesForClasses() {
    $this->proxyInstanceFor(array(XPClass::forName('lang.Object')));
  }
  
  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannotCreateProxiesForClassesAsSecondArg() {
    $this->proxyInstanceFor(array(
      XPClass::forName('util.XPIterator'),
      XPClass::forName('lang.Object')
    ));
  }

  #[@test]
  public function allowDoubledInterfaceMethod() {
    $this->proxyInstanceFor(array(
      XPClass::forName('util.XPIterator'),
      ClassLoader::defineInterface('util.NewIterator', 'util.XPIterator')
    ));
  }
  
  #[@test]
  public function overloadedMethod() {
    $proxy= $this->proxyInstanceFor(array(XPClass::forName('net.xp_framework.unittest.reflection.OverloadedInterface')));
    $proxy->overloaded('foo');
    $proxy->overloaded('foo', 'bar');
    $this->assertEquals(array('foo'), $this->handler->invocations['overloaded_1']);
    $this->assertEquals(array('foo', 'bar'), $this->handler->invocations['overloaded_2']);
  }    
}
