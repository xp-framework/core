<?php namespace net\xp_framework\unittest\reflection;

use lang\{XPClass, Type, ClassLoader, IllegalArgumentException, Error};
use lang\reflect\{Proxy, InvocationHandler};
use util\{XPIterator, Observer};
use unittest\actions\RuntimeVersion;

/**
 * Tests the Proxy class
 *
 * @see   xp://lang.reflect.Proxy
 */
class ProxyTest extends \unittest\TestCase {
  protected $handler, $iteratorClass, $observerClass;

  /** @return void */
  public function setUp() {
    $this->handler= new class() implements InvocationHandler {
      public $invocations = [];
      public function invoke($proxy, $method, $args) {
        $this->invocations[$method.'_'.sizeof($args)]= $args;
      }
    };
    $this->iteratorClass= XPClass::forName(XPIterator::class);
    $this->observerClass= XPClass::forName(Observer::class);
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
    return $this->proxyClassFor([ClassLoader::defineInterface(
      'net.xp_framework.unittest.reflection.__NP_'.$this->name,
      [],
      $body
    )]);
  }

  #[@test, @expect(Error::class)]
  public function nullClassLoader() {
    Proxy::getProxyClass(null, [$this->iteratorClass]);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function emptyInterfaces() {
    Proxy::getProxyClass(ClassLoader::getDefault(), []);
  }

  #[@test, @expect(Error::class)]
  public function nullInterfaces() {
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
    $this->assertEquals(1, sizeof($interfaces));
    $this->assertTrue(in_array($this->iteratorClass, $interfaces)); 
  }

  #[@test]
  public function allInterfacesAreImplemented() {
    $class= $this->proxyClassFor([$this->iteratorClass, $this->observerClass]);
    $interfaces= $class->getInterfaces();
    $this->assertEquals(2, sizeof($interfaces));
    $this->assertTrue(in_array($this->iteratorClass, $interfaces));
    $this->assertTrue(in_array($this->observerClass, $interfaces));
  }

  #[@test]
  public function iteratorMethods() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    $this->assertEquals(
      [true, true],
      [$class->hasMethod('hasNext'), $class->hasMethod('next')]
    );
  }

  #[@test]
  public function iteratorNextInvoked() {
    $proxy= $this->proxyInstanceFor([$this->iteratorClass]);
    $proxy->next();
    $this->assertEquals([], $this->handler->invocations['next_0']);
  }
  
  #[@test, @expect(IllegalArgumentException::class)]
  public function cannotCreateProxiesForClasses() {
    $this->proxyInstanceFor([XPClass::forName('lang.Type')]);
  }
  
  #[@test, @expect(IllegalArgumentException::class)]
  public function cannotCreateProxiesForClassesAsSecondArg() {
    $this->proxyInstanceFor([
      $this->iteratorClass,
      XPClass::forName('lang.Type')
    ]);
  }

  #[@test]
  public function allowDoubledInterfaceMethod() {
    $this->proxyInstanceFor([
      $this->iteratorClass,
      ClassLoader::defineInterface('util.NewIterator', XPIterator::class)
    ]);
  }
  
  #[@test]
  public function overloadedMethod() {
    $proxy= $this->proxyInstanceFor([XPClass::forName(OverloadedInterface::class)]);
    $proxy->overloaded('foo');
    $proxy->overloaded('foo', 'bar');
    $this->assertEquals(['foo'], $this->handler->invocations['overloaded_1']);
    $this->assertEquals(['foo', 'bar'], $this->handler->invocations['overloaded_2']);
  }

  #[@test]
  public function namespaced_typehinted_parameters_handled_correctly() {
    $proxy= $this->newProxyWith('{ public function fixture(\util\Date $param); }');
    $this->assertEquals(
      XPClass::forName('util.Date'),
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
      Type::$ARRAY,
      $proxy->getMethod('fixture')->getParameters()[0]->getTypeRestriction()
    );
  }
}
