<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;

/**
 * Test the XP reflection API
 *
 * @see  xp://lang.XPClass
 */
class ReflectionTest extends \unittest\TestCase {
  protected $class;

  /**
   * Setup method
   */
  public function setUp() {
    $this->class= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
  }
 
  #[@test]
  public function name() {
    $this->assertEquals(
      'net.xp_framework.unittest.reflection.TestClass', 
      $this->class->getName()
    );
  }

  #[@test]
  public function instanciation() {
    $instance= $this->class->newInstance(1);
    $this->assertObject($instance);
    $this->assertClass($instance, 'net.xp_framework.unittest.reflection.TestClass');
    $this->assertTrue($this->class->isInstance($instance));
  }
  
  #[@test]
  public function subClass() {
    $this->assertTrue($this->class->isSubclassOf('lang.Object'));
    $this->assertFalse($this->class->isSubclassOf('util.Date'));
    $this->assertFalse($this->class->isSubclassOf('net.xp_framework.unittest.reflection.TestClass'));
  }

  #[@test]
  public function subClassOfClass() {
    $this->assertTrue($this->class->isSubclassOf(XPClass::forName('lang.Object')));
    $this->assertFalse($this->class->isSubclassOf(XPClass::forName('util.Date')));
    $this->assertFalse($this->class->isSubclassOf(XPClass::forName('net.xp_framework.unittest.reflection.TestClass')));
  }

  #[@test]
  public function selfIsAssignableFromFixture() {
    $this->assertTrue($this->class->isAssignableFrom($this->class));
  }

  #[@test]
  public function objectIsAssignableFromFixture() {
    $this->assertTrue(XPClass::forName('lang.Object')->isAssignableFrom($this->class));
  }

  #[@test]
  public function parentIsAssignableFromFixture() {
    $this->assertTrue(XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')->isAssignableFrom($this->class));
  }

  #[@test]
  public function thisIsNotAssignableFromFixture() {
    $this->assertFalse($this->getClass()->isAssignableFrom($this->class));
  }

  #[@test]
  public function primitiveIsNotAssignableFromFixture() {
    $this->assertFalse($this->getClass()->isAssignableFrom('int'));
  }

  #[@test]
  public function primitiveTypeIsNotAssignableFromFixture() {
    $this->assertFalse($this->getClass()->isAssignableFrom(\lang\Primitive::$INT));
  }

  #[@test, @expect('lang.IllegalStateException')]
  public function isAssignableFromIllegalArgument() {
    $this->getClass()->isAssignableFrom('@not-a-type@');
  }

  #[@test]
  public function parentClass() {
    $parent= $this->class->getParentClass();
    $this->assertClass($parent, 'lang.XPClass');
    $this->assertEquals('net.xp_framework.unittest.reflection.AbstractTestClass', $parent->getName());
    $this->assertEquals('lang.Object', $parent->getParentClass()->getName());
    $this->assertNull($parent->getParentClass()->getParentClass());
  }

  #[@test]
  public function interfaces() {
    $this->assertFalse($this->class->isInterface());
    $interfaces= $this->class->getInterfaces();
    $this->assertArray($interfaces);
    foreach ($interfaces as $interface) {
      $this->assertClass($interface, 'lang.XPClass');
      $this->assertTrue($interface->isInterface());
    }
  }

  #[@test]
  public function testClassDeclaredInterfaces() {
    $this->assertEquals(
      array(XPClass::forName('util.log.Traceable')), 
      $this->class->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function objectClassDeclaredInterfaces() {
    $this->assertEquals(
      array(XPClass::forName('lang.Generic')), 
      XPClass::forName('lang.Object')->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function thisClassDeclaredInterfaces() {
    $this->assertEquals(
      array(), 
      $this->getClass()->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function ilistInterfaceDeclaredInterfaces() {
    $this->assertEquals(
      array(new XPClass('ArrayAccess'), new XPClass('IteratorAggregate')), 
      XPClass::forName('util.collections.IList')->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function constructor() {
    $this->assertTrue($this->class->hasConstructor());
    $this->assertClass($this->class->getConstructor(), 'lang.reflect.Constructor');
  }

  #[@test]
  public function checkNoConstructor() {
    $this->assertFalse(XPClass::forName('lang.Object')->hasConstructor());
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function noConstructor() {
    XPClass::forName('lang.Object')->getConstructor();
  }

  #[@test]
  public function constructorInvocation() {
    $instance= $this->class->getConstructor()->newInstance(array('1977-12-14'));
    $this->assertEquals($this->class, $instance->getClass());
    $this->assertEquals(new \util\Date('1977-12-14'), $instance->getDate());
  }

  #[@test, @expect('lang.reflect.TargetInvocationException')]
  public function constructorInvocationFailure() {
    $this->class->getConstructor()->newInstance(array('@@not-a-valid-date-string@@'));
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function abstractConstructorInvocation() {
    XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')
      ->getConstructor()
      ->newInstance()
    ;
  }
  
  #[@test]
  public function implementedConstructorInvocation() {
    $i= \lang\ClassLoader::defineClass('ANonAbstractClass', 'net.xp_framework.unittest.reflection.AbstractTestClass', array(), '{
      public function getDate() {}
    }');
    
    $this->assertSubclass($i->getConstructor()->newInstance(), 'net.xp_framework.unittest.reflection.AbstractTestClass');
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function newInstanceForAbstractClass() {
    XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')->newInstance();
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function newInstanceForInterface() {
    XPClass::forName('util.log.Traceable')->newInstance();
  }

  #[@test]
  public function annotations() {
    $this->assertTrue($this->class->hasAnnotations());
    $annotations= $this->class->getAnnotations();
    $this->assertArray($annotations);
  }

  #[@test]
  public function testAnnotation() {
    $this->assertTrue($this->class->hasAnnotation('test'));
    $this->assertEquals('Annotation', $this->class->getAnnotation('test'));
  }
  
  #[@test, @expect('lang.ElementNotFoundException')]
  public function nonExistantAnnotation() {
    $this->class->getAnnotation('non-existant');
  }
  
  #[@test]
  public function forName() {
    $class= XPClass::forName('util.Date');
    $this->assertClass($class, 'lang.XPClass');
    $this->assertEquals('util.Date', $class->getName());
  }
  
  #[@test, @expect('lang.ClassNotFoundException')]
  public function nonExistantforName() {
    XPClass::forName('class.does.not.Exist');
  }

  #[@test]
  public function getClasses() {
    $this->assertInstanceOf('lang.XPClass[]', XPClass::getClasses());
  }
  
  #[@test]
  public function getConstants() {
    $this->assertEquals(
      array('CONSTANT_STRING' => 'XP Framework', 'CONSTANT_INT' => 15, 'CONSTANT_NULL' => null),
      $this->class->getConstants()
    );
  }

  #[@test]
  public function hasConstantString() {
    $this->assertTrue($this->class->hasConstant('CONSTANT_STRING'));
  }

  #[@test]
  public function getConstantString() {
    $this->assertEquals('XP Framework', $this->class->getConstant('CONSTANT_STRING'));
  }

  #[@test]
  public function hasConstantInt() {
    $this->assertTrue($this->class->hasConstant('CONSTANT_INT'));
  }

  #[@test]
  public function getConstantInt() {
    $this->assertEquals(15, $this->class->getConstant('CONSTANT_INT'));
  }

  #[@test]
  public function hasConstantNull() {
    $this->assertTrue($this->class->hasConstant('CONSTANT_NULL'));
  }

  #[@test]
  public function getConstantNull() {
    $this->assertEquals(null, $this->class->getConstant('CONSTANT_NULL'));
  }

  #[@test]
  public function checkNonexistingConstant() {
    $this->assertFalse($this->class->hasConstant('DOES_NOT_EXIST'));
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function retrieveNonexistingConstant() {
    $this->class->getConstant('DOES_NOT_EXIST');
  }
}
