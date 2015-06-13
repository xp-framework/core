<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\Proxy;
use lang\XPClass;
use lang\ClassLoader;
use lang\reflect\InvocationHandler;
use util\XPIterator;
use util\Observer;
use unittest\actions\RuntimeVersion;

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
    $this->handler= newinstance('lang.reflect.InvocationHandler', [], [
      'invocations' => [],
      'invoke'      => function($proxy, $method, $args) {
        $this->invocations[$method.'_'.sizeof($args)]= $args;
      }
    ]);
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
    return Proxy::newProxyInstance(ClassLoader::getDefault(), $interfaces, $this->handler);
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
    return Proxy::getProxyClass(ClassLoader::getDefault(), $interfaces);
  }

  /**
   * Helper method which returns a proxy class with a unique name and
   * a given body, using the default classloader.
   *
   * @param   string body
   * @return  lang.XPClass
   */
  protected function newProxyWith($body) {
    return $this->proxyClassFor(array(ClassLoader::defineInterface(
      'net.xp_framework.unittest.reflection.__NP_'.$this->name,
      array(),
      $body
    )));
  }

  #[@test, @expect('lang.IllegalArgumentException'), @action(new RuntimeVersion('<7.0.0alpha1'))]
  public function nullClassLoader() {
    Proxy::getProxyClass(null, [$this->iteratorClass]);
  }

  #[@test, @expect('lang.Error'), @action(new RuntimeVersion('>=7.0.0alpha1'))]
  public function nullClassLoader7() {
    Proxy::getProxyClass(null, [$this->iteratorClass]);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function emptyInterfaces() {
    Proxy::getProxyClass(ClassLoader::getDefault(), []);
  }

  #[@test, @expect('lang.IllegalArgumentException'), @action(new RuntimeVersion('<7.0.0alpha1'))]
  public function nullInterfaces() {
    Proxy::getProxyClass(ClassLoader::getDefault(), null);
  }

  #[@test, @expect('lang.Error'), @action(new RuntimeVersion('>=7.0.0alpha1'))]
  public function nullInterfaces7() {
    Proxy::getProxyClass(ClassLoader::getDefault(), null);
  }

  #[@test]
  public function proxyClassNamesGetPrefixed() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    $this->assertEquals(Proxy::PREFIX, substr($class->getName(), 0, strlen(Proxy::PREFIX)));
  }

  #[@test]
  public function classesEqualForSameInterfaceList() {
    $this->assertEquals(
      $this->proxyClassFor([$this->iteratorClass]),
      $this->proxyClassFor([$this->iteratorClass])
    );
  }

  #[@test]
  public function classesNotEqualForDifferingInterfaceList() {
    $this->assertNotEquals(
      $this->proxyClassFor([$this->iteratorClass]),
      $this->proxyClassFor([$this->iteratorClass, $this->observerClass])
    );
  }

  #[@test]
  public function iteratorInterfaceIsImplemented() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    $interfaces= $class->getInterfaces();
    $this->assertEquals(2, sizeof($interfaces));
    $this->assertTrue(in_array($this->iteratorClass, $interfaces)); 
  }

  #[@test]
  public function allInterfacesAreImplemented() {
    $class= $this->proxyClassFor([$this->iteratorClass, $this->observerClass]);
    $interfaces= $class->getInterfaces();
    $this->assertEquals(3, sizeof($interfaces));
    $this->assertTrue(in_array($this->iteratorClass, $interfaces));
    $this->assertTrue(in_array($this->observerClass, $interfaces));
  }

  #[@test]
  public function iteratorMethods() {
    $expected= [
      'hashcode', 'equals', 'getclassname', 'getclass', 'tostring', // lang.Object
      'getproxyclass', 'newproxyinstance',                          // lang.reflect.Proxy
      'hasnext', 'next'                                             // util.XPIterator
    ];
    
    $class= $this->proxyClassFor([$this->iteratorClass]);
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
    $proxy= $this->proxyInstanceFor([$this->iteratorClass]);
    $proxy->next();
    $this->assertEquals([], $this->handler->invocations['next_0']);
  }
  
  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannotCreateProxiesForClasses() {
    $this->proxyInstanceFor([XPClass::forName('lang.Object')]);
  }
  
  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannotCreateProxiesForClassesAsSecondArg() {
    $this->proxyInstanceFor([
      XPClass::forName('util.XPIterator'),
      XPClass::forName('lang.Object')
    ]);
  }

  #[@test]
  public function allowDoubledInterfaceMethod() {
    $this->proxyInstanceFor([
      XPClass::forName('util.XPIterator'),
      ClassLoader::defineInterface('util.NewIterator', 'util.XPIterator')
    ]);
  }
  
  #[@test]
  public function overloadedMethod() {
    $proxy= $this->proxyInstanceFor([XPClass::forName('net.xp_framework.unittest.reflection.OverloadedInterface')]);
    $proxy->overloaded('foo');
    $proxy->overloaded('foo', 'bar');
    $this->assertEquals(['foo'], $this->handler->invocations['overloaded_1']);
    $this->assertEquals(['foo', 'bar'], $this->handler->invocations['overloaded_2']);
  }

  #[@test]
  public function namespaced_typehinted_parameters_handled_correctly() {
    $proxy= $this->newProxyWith('{ public function fixture(\lang\types\Long $param); }');
    $this->assertEquals(
      XPClass::forName('lang.types.Long'),
      $proxy->getMethod('fixture')->getParameters()[0]->getTypeRestriction()
    );
  }

  #[@test]
  public function builtin_typehinted_parameters_handled_correctly() {
    $proxy= $this->newProxyWith('{ public function fixture(\ReflectionClass $param); }');
    $this->assertEquals(
      new XPClass('ReflectionClass'),
      $proxy->getMethod('fixture')->getParameters()[0]->getTypeRestriction()
    );
  }

  #[@test]
  public function builtin_array_parameters_handled_correctly() {
    $proxy= $this->newProxyWith('{ public function fixture(array $param); }');
    $this->assertEquals(
      \lang\Type::$ARRAY,
      $proxy->getMethod('fixture')->getParameters()[0]->getTypeRestriction()
    );
  }
}
