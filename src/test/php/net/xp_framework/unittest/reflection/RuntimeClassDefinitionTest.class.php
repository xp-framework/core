<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use util\log\Traceable;
use lang\reflect\Proxy;

/**
 * TestCase
 *
 * @see   xp://lang.CLassLoader
 * @see   https://github.com/xp-framework/xp-framework/issues/94
 */
class RuntimeClassDefinitionTest extends TestCase {

  /**
   * Helper method
   *
   * @param   string name
   * @param   string parent
   * @param   string[] interfaces
   * @return  lang.XPClass class
   * @throws  unittest.AssertionFailedError
   */
  protected function defineClass($name, $parent, $interfaces, $bytes) {
    if (class_exists(\xp::reflect($name), false)) {
      $this->fail('Class "'.$name.'" may not exist!');
    }
    return \lang\ClassLoader::defineClass($name, $parent, $interfaces, $bytes);
  }

  /**
   * Helper method
   *
   * @param   string name
   * @param   lang.XPClass class
   * @throws  unittest.AssertionFailedError
   */
  protected function defineInterface($name, $parents, $bytes) {
    if (interface_exists(\xp::reflect($name), false)) {
      $this->fail('Interface "'.$name.'" may not exist!');
    }
    return \lang\ClassLoader::defineInterface($name, $parents, $bytes);
  }

  #[@test]
  public function defineClassWithInitializer() {
    $name= 'net.xp_framework.unittest.reflection.RuntimeDefinedClass';
    $class= $this->defineClass($name, 'lang.Object', null, '{
      public static $initializerCalled= FALSE;
      
      static function __static() { 
        self::$initializerCalled= TRUE; 
      }
    }');
    $this->assertEquals($name, $class->getName());
    $this->assertTrue(RuntimeDefinedClass::$initializerCalled);
    $this->assertInstanceOf('lang.DynamicClassLoader', $class->getClassLoader());
  }
  
  #[@test]
  public function defineTraceableClass() {
    $name= 'net.xp_framework.unittest.reflection.RuntimeDefinedClassWithInterface';
    $class= $this->defineClass($name, 'lang.Object', array('util.log.Traceable'), '{
      public function setTrace($cat) { } 
    }');

    $this->assertTrue($class->isSubclassOf('util.log.Traceable'));
    $this->assertInstanceOf('lang.DynamicClassLoader', $class->getClassLoader());
  }

  #[@test]
  public function defineClassWithXPClassParent() {
    $parent= \lang\XPClass::forName('lang.Object');
    $class= $this->defineClass('_'.$this->name, $parent, array(), '{ }');
    $this->assertEquals($parent, $class->getParentclass());
  }

  #[@test]
  public function defineClassWithXPClassInterface() {
    $interface= \lang\XPClass::forName('lang.Runnable');
    $class= $this->defineClass('_'.$this->name, 'lang.Object', array($interface), '{
      public function run() { }
    }');
    $this->assertEquals($interface, this($class->getDeclaredInterfaces(), 0));
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function defineClassWithNonExistantParent() {
    $name= 'net.xp_framework.unittest.reflection.ErroneousClass';
    $this->defineClass($name, '@@nonexistant@@', array(), '{ }');
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function defineClassWithOneNonExistantInterface() {
    $name= 'net.xp_framework.unittest.reflection.ErroneousClass';
    $this->defineClass($name, 'lang.Object', array('@@nonexistant@@'), '{ }');
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function defineClassWithOneExistantAndOneNonExistantInterface() {
    $name= 'net.xp_framework.unittest.reflection.ErroneousClass';
    $this->defineClass($name, 'lang.Object', array('lang.Runnable', '@@nonexistant@@'), '{ }');
  }

  #[@test]
  public function newInstance() {
    $i= newinstance('lang.Object', array(), '{ public function bar() { return TRUE; }}');
    $this->assertInstanceOf('lang.DynamicClassLoader', $i->getClass()->getClassLoader());
  }

  #[@test]
  public function proxyInstance() {
    $c= Proxy::getProxyClass(
      \lang\ClassLoader::getDefault(), 
      array(\lang\XPClass::forName('util.log.Traceable'))
    );
    $this->assertInstanceOf('lang.DynamicClassLoader', $c->getClassLoader());
  }

  #[@test]
  public function defineSimpleInterface() {
    $name= 'net.xp_framework.unittest.reflection.SimpleInterface';
    $class= $this->defineInterface($name, array(), '{
      public function setTrace($cat);
    }');
    $this->assertEquals($name, $class->getName());
    $this->assertTrue($class->isInterface());
    $this->assertEquals(array(), $class->getInterfaces());
    $this->assertInstanceOf('lang.DynamicClassLoader', $class->getClassLoader());
  }

  #[@test]
  public function defineInterfaceWithParent() {
    $name= 'net.xp_framework.unittest.reflection.InterfaceWithParent';
    $class= $this->defineInterface($name, array('util.log.Traceable'), '{
      public function setDebug($cat);
    }');
    $this->assertEquals($name, $class->getName());
    $this->assertTrue($class->isInterface());
    $this->assertEquals(array(\lang\XPClass::forName('util.log.Traceable')), $class->getInterfaces());
    $this->assertInstanceOf('lang.DynamicClassLoader', $class->getClassLoader());
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function defineInterfaceWithNonExistantParent() {
    $name= 'net.xp_framework.unittest.reflection.ErroneousInterface';
    $this->defineInterface($name, array('@@nonexistant@@'), '{ }');
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function defineInterfaceWithOneExistantAndOneNonExistantParent() {
    $name= 'net.xp_framework.unittest.reflection.ErroneousInterface';
    $this->defineInterface($name, array('lang.Runnable', '@@nonexistant@@'), '{ }');
  }

  #[@test]
  public function defaultClassLoaderProvidesDefinedClass() {
    $class= 'net.xp_framework.unittest.reflection.lostandfound.CL1';
    $this->defineClass($class, 'lang.Object', array(), '{ }');

    $this->assertTrue(\lang\ClassLoader::getDefault()->providesClass($class));
  }

  #[@test]
  public function defaultClassLoaderProvidesDefinedInterface() {
    $class= 'net.xp_framework.unittest.reflection.lostandfound.IF1';
    $this->defineInterface($class, array(), '{ }');

    $this->assertTrue(\lang\ClassLoader::getDefault()->providesClass($class));
  }

  #[@test]
  public function defaultClassLoaderProvidesPackageOfDefinedClass() {
    $package= 'net.xp_framework.unittest.reflection.lostandfound';
    $this->defineClass($package.'.CL2', 'lang.Object', array(), '{ }');

    $this->assertTrue(\lang\ClassLoader::getDefault()->providesPackage($package));
  }
}
