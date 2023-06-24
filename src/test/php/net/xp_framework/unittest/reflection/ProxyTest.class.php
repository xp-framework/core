<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\{InvocationHandler, Proxy};
use lang\{ClassLoader, Error, IllegalArgumentException, Type, XPClass};
use unittest\Assert;
use unittest\actions\RuntimeVersion;
use unittest\{Expect, Test};
use util\{Observer, XPIterator};

/**
 * Tests the Proxy class
 *
 * @see   xp://lang.reflect.Proxy
 */
class ProxyTest {
  protected $handler, $iteratorClass, $observerClass;

  /** @return void */
  #[Before]
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
    static $uniq= 0;

    return $this->proxyClassFor([ClassLoader::defineInterface(
      'net.xp_framework.unittest.reflection.__NP_'.($uniq++),
      [],
      $body
    )]);
  }

  #[Test, Expect(Error::class)]
  public function nullClassLoader() {
    Proxy::getProxyClass(null, [$this->iteratorClass]);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function emptyInterfaces() {
    Proxy::getProxyClass(ClassLoader::getDefault(), []);
  }

  #[Test, Expect(Error::class)]
  public function nullInterfaces() {
    Proxy::getProxyClass(ClassLoader::getDefault(), null);
  }

  #[Test]
  public function proxyClassNamesGetPrefixed() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    Assert::equals(Proxy::PREFIX, substr($class->getName(), 0, strlen(Proxy::PREFIX)));
  }

  #[Test]
  public function classesEqualForSameInterfaceList() {
    Assert::equals(
      $this->proxyClassFor([$this->iteratorClass]),
      $this->proxyClassFor([$this->iteratorClass])
    );
  }

  #[Test]
  public function classesNotEqualForDifferingInterfaceList() {
    Assert::notEquals(
      $this->proxyClassFor([$this->iteratorClass]),
      $this->proxyClassFor([$this->iteratorClass, $this->observerClass])
    );
  }

  #[Test]
  public function iteratorInterfaceIsImplemented() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    Assert::equals([$this->iteratorClass], $class->getInterfaces());
  }

  #[Test]
  public function allInterfacesAreImplemented() {
    $class= $this->proxyClassFor([$this->iteratorClass, $this->observerClass]);
    Assert::equals([$this->iteratorClass, $this->observerClass], $class->getInterfaces());
  }

  #[Test]
  public function iteratorMethods() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    Assert::equals(
      [true, true],
      [$class->hasMethod('hasNext'), $class->hasMethod('next')]
    );
  }

  #[Test]
  public function iteratorNextInvoked() {
    $proxy= $this->proxyInstanceFor([$this->iteratorClass]);
    $proxy->next();
    Assert::equals([], $this->handler->invocations['next_0']);
  }
  
  #[Test, Expect(IllegalArgumentException::class)]
  public function cannotCreateProxiesForClasses() {
    $this->proxyInstanceFor([XPClass::forName('lang.Type')]);
  }
  
  #[Test, Expect(IllegalArgumentException::class)]
  public function cannotCreateProxiesForClassesAsSecondArg() {
    $this->proxyInstanceFor([
      $this->iteratorClass,
      XPClass::forName('lang.Type')
    ]);
  }

  #[Test]
  public function allowDoubledInterfaceMethod() {
    $this->proxyInstanceFor([
      $this->iteratorClass,
      ClassLoader::defineInterface('util.NewIterator', XPIterator::class)
    ]);
  }
  
  #[Test]
  public function overloadedMethod() {
    $proxy= $this->proxyInstanceFor([XPClass::forName(OverloadedInterface::class)]);
    $proxy->overloaded('foo');
    $proxy->overloaded('foo', 'bar');
    Assert::equals(['foo'], $this->handler->invocations['overloaded_1']);
    Assert::equals(['foo', 'bar'], $this->handler->invocations['overloaded_2']);
  }

  #[Test]
  public function namespaced_typehinted_parameters_handled_correctly() {
    $proxy= $this->newProxyWith('{ public function fixture(\util\Date $param); }');
    Assert::equals(
      XPClass::forName('util.Date'),
      $proxy->getMethod('fixture')->getParameters()[0]->getTypeRestriction()
    );
  }

  #[Test]
  public function builtin_typehinted_parameters_handled_correctly() {
    $proxy= $this->newProxyWith('{ public function fixture(\ReflectionClass $param); }');
    Assert::equals(
      new XPClass('ReflectionClass'),
      $proxy->getMethod('fixture')->getParameters()[0]->getTypeRestriction()
    );
  }

  #[Test]
  public function builtin_array_parameters_handled_correctly() {
    $proxy= $this->newProxyWith('{ public function fixture(array $param); }');
    Assert::equals(
      Type::$ARRAY,
      $proxy->getMethod('fixture')->getParameters()[0]->getTypeRestriction()
    );
  }
}