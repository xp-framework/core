<?php namespace net\xp_framework\unittest\tests\mock;

use lang\IllegalArgumentException;
use lang\Error;
use unittest\TestCase;
use unittest\mock\MockProxyBuilder;
use util\XPIterator;
use lang\reflect\InvocationHandler;
use unittest\actions\RuntimeVersion;

/**
 * Tests the Proxy class
 *
 * @see   xp://lang.reflect.Proxy
 */
class MockProxyBuilderTest extends TestCase {
  public
    $handler       = null,
    $iteratorClass = null,
    $observerClass = null;

  /**
   * Setup method 
   */
  public function setUp() {
    $this->handler= newinstance('lang.reflect.InvocationHandler', [], '{
      public $invocations= [];

      public function invoke($proxy, $method, $args) { 
        $this->invocations[$method."_".sizeof($args)]= $args;
      }
    }');
    $this->iteratorClass= \lang\XPClass::forName('util.XPIterator');
    $this->observerClass= \lang\XPClass::forName('util.Observer');
    $this->staticInitializerClass= newinstance('lang.Object', [], '{
      private static $counter= 0;
      static function __static() {
        self::$counter++;
      }

      public function counter() {
        return self::$counter;
      }
    }');
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
    return (new MockProxyBuilder())->createProxyInstance(
      \lang\ClassLoader::getDefault(),
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
    return (new MockProxyBuilder())->createProxyClass(
      \lang\ClassLoader::getDefault(),
      $interfaces
    );
  }

  #[@test, @expect(IllegalArgumentException::class), @action(new RuntimeVersion('<7.0.0-dev'))]
  public function nullClassLoader() {
    (new MockProxyBuilder())->createProxyClass(null, [$this->iteratorClass]);
  }

  #[@test, @expect(IllegalArgumentException::class), @action(new RuntimeVersion('<7.0.0-dev'))]
  public function nullInterfaces() {
    (new MockProxyBuilder())->createProxyClass(\lang\ClassLoader::getDefault(), null);
  }

  #[@test, @expect(Error::class), @action(new RuntimeVersion('>=7.0.0-dev'))]
  public function nullClassLoader7() {
    (new MockProxyBuilder())->createProxyClass(null, [$this->iteratorClass]);
  }

  #[@test, @expect(Error::class), @action(new RuntimeVersion('>=7.0.0-dev'))]
  public function nullInterfaces7() {
    (new MockProxyBuilder())->createProxyClass(\lang\ClassLoader::getDefault(), null);
  }

  #[@test]
  public function proxyClassNamesGetPrefixed() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    $this->assertEquals(MockProxyBuilder::PREFIX, substr($class->getName(), 0, strlen(MockProxyBuilder::PREFIX)));
  }

  #[@test]
  public function classesEqualForSameInterfaceList() {
    $c1= $this->proxyClassFor([$this->iteratorClass]);
    $c2= $this->proxyClassFor([$this->iteratorClass]);
    $c3= $this->proxyClassFor([$this->iteratorClass, $this->observerClass]);

    $this->assertEquals($c1, $c2);
    $this->assertNotEquals($c1, $c3);
  }

  #[@test]
  public function iteratorInterfaceIsImplemented() {
    $class= $this->proxyClassFor([$this->iteratorClass]);
    $interfaces= $class->getInterfaces();
    $this->assertEquals(3, sizeof($interfaces)); //lang.Generic, lang.reflect.IProxy, util.XPIterator
    $this->assertTrue(in_array($this->iteratorClass, $interfaces));
  }

  #[@test]
  public function allInterfacesAreImplemented() {
    $class= $this->proxyClassFor([$this->iteratorClass, $this->observerClass]);
    $interfaces= $class->getInterfaces();
    $this->assertEquals(4, sizeof($interfaces));
    $this->assertTrue(in_array($this->iteratorClass, $interfaces));
    $this->assertTrue(in_array($this->observerClass, $interfaces));
  }

  #[@test]
  public function iteratorMethods() {
    $expected= [
      'hashcode', 'equals', 'getclassname', 'getclass', 'tostring', // lang.Object
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
  
  #[@test, @expect(IllegalArgumentException::class)]
  public function cannotCreateProxiesForClasses() {
    $this->proxyInstanceFor([\lang\XPClass::forName('lang.Object')]);
  }
  
  #[@test]
  public function allowDoubledInterfaceMethod() {
    $newIteratorClass= \lang\ClassLoader::defineInterface('util.NewIterator', 'util.XPIterator');
    $this->proxyInstanceFor([\lang\XPClass::forName('util.XPIterator'), $newIteratorClass]);
  }
  
  #[@test]
  public function overloadedMethod() {
    $proxy= $this->proxyInstanceFor([\lang\XPClass::forName('net.xp_framework.unittest.reflection.OverloadedInterface')]);
    $proxy->overloaded('foo');
    $proxy->overloaded('foo', 'bar');
    $this->assertEquals(['foo'], $this->handler->invocations['overloaded_1']);
    $this->assertEquals(['foo', 'bar'], $this->handler->invocations['overloaded_2']);
  }

  #[@test]
  public function static_initializer_gets_overwritten() {
    $class= (new MockProxyBuilder())->createProxyClass(\lang\ClassLoader::getDefault(), [], $this->staticInitializerClass->getClass());
    $obj= $class->newInstance(null);

    $this->assertEquals(1, $obj->counter());
  }

  #[@test]
  public function proxyClass_implements_IMockProxy() {
    $proxy= $this->proxyClassFor([$this->iteratorClass]);
    $interfaces= $proxy->getInterfaces();
    $this->assertTrue(in_array(\lang\XPClass::forName('unittest.mock.IMockProxy'), $interfaces));
  }

  #[@test]
  public function concrete_methods_should_not_be_changed_by_default() {
    $proxyBuilder= new MockProxyBuilder();
    $class= $proxyBuilder->createProxyClass(\lang\ClassLoader::getDefault(),
      [],
      \lang\XPClass::forName('net.xp_framework.unittest.tests.mock.AbstractDummy')
    );
    $proxy= $class->newInstance($this->handler);
    $this->assertEquals('concreteMethod', $proxy->concreteMethod());
  }

  #[@test]
  public function abstract_methods_should_delegated_to_handler() {
    $proxyBuilder= new MockProxyBuilder();
    $class= $proxyBuilder->createProxyClass(\lang\ClassLoader::getDefault(),
      [],
      \lang\XPClass::forName('net.xp_framework.unittest.tests.mock.AbstractDummy')
    );
    $proxy= $class->newInstance($this->handler);
    $proxy->abstractMethod();
    $this->assertInstanceOf('var[]', $this->handler->invocations['abstractMethod_0']);
  }

  #[@test]
  public function with_overwriteAll_abstract_methods_should_delegated_to_handler() {
    $proxyBuilder= new MockProxyBuilder();
    $proxyBuilder->setOverwriteExisting(true);
    $class= $proxyBuilder->createProxyClass(\lang\ClassLoader::getDefault(),
      [],
      \lang\XPClass::forName('net.xp_framework.unittest.tests.mock.AbstractDummy')
    );
    $proxy= $class->newInstance($this->handler);
    $proxy->concreteMethod();
    $this->assertInstanceOf('var[]', $this->handler->invocations['concreteMethod_0']);
  }

  #[@test]
  public function reserved_methods_should_not_be_overridden() {
    $proxyBuilder= new MockProxyBuilder();
    $proxyBuilder->setOverwriteExisting(true);
    $class= $proxyBuilder->createProxyClass(\lang\ClassLoader::getDefault(),
      [],
      \lang\XPClass::forName('net.xp_framework.unittest.tests.mock.AbstractDummy')
    );
    $proxy= $class->newInstance($this->handler);
    $proxy->equals(new \lang\Object());
    $this->assertFalse(isset($this->handler->invocations['equals_1']));
  }

  #[@test]
  public function namespaced_parameters_handled_correctly() {
    $class= $this->proxyClassFor([\lang\ClassLoader::defineInterface('net.xp_framework.unittest.test.mock.NSInterface', [], '{
      public function fixture(\lang\types\Long $param);
    }')]);
    $this->assertEquals(
      \lang\XPClass::forName('lang.types.Long'),
      $class->getMethod('fixture')->getParameters()[0]->getType()
    );
  }
}
