<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Primitive;

/**
 * Test the XPClass class, the entry point to the XP Framework's class reflection API.
 *
 * @see  xp://lang.XPClass
 */
class XPClassTest extends \unittest\TestCase {
  protected $fixture;

  /**
   * Setup method
   */
  public function setUp() {
    $this->fixture= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
  }
 
  #[@test]
  public function getName_returns_fully_qualified_name() {
    $this->assertEquals('net.xp_framework.unittest.reflection.TestClass', $this->fixture->getName());
  }

  #[@test]
  public function literal_returns_name_as_known_to_PHP() {
    $this->assertEquals(get_class(new TestClass()), $this->fixture->literal());
  }

  #[@test]
  public function getSimpleName_returns_class_name_only() {
    $this->assertEquals('TestClass', $this->fixture->getSimpleName());
  }

  #[@test]
  public function getPackage_returns_package_class_resides_in() {
    $this->assertEquals(
      \lang\reflect\Package::forName('net.xp_framework.unittest.reflection'),
      $this->fixture->getPackage()
    );
  }

  #[@test]
  public function newInstance_creates_instances_of_class() {
    $this->assertEquals(new TestClass(1), $this->fixture->newInstance(1));
  }

  #[@test]
  public function instance_created_with_new_is_instance_of_class() {
    $this->assertTrue($this->fixture->isInstance(new TestClass(1)));
  }
  
  #[@test]
  public function is_subclass_of_lang_Object() {
    $this->assertTrue($this->fixture->isSubclassOf('lang.Object'));
  }

  #[@test]
  public function is_not_subclass_of_util_Date() {
    $this->assertFalse($this->fixture->isSubclassOf('util.Date'));
  }

  #[@test]
  public function is_not_subclass_of_itself() {
    $this->assertFalse($this->fixture->isSubclassOf('net.xp_framework.unittest.reflection.TestClass'));
  }

  #[@test]
  public function class_is_assignable_from_itself() {
    $this->assertTrue($this->fixture->isAssignableFrom($this->fixture));
  }

  #[@test]
  public function object_class_is_assignable_from_fixture() {
    $this->assertTrue(XPClass::forName('lang.Object')->isAssignableFrom($this->fixture));
  }

  #[@test]
  public function fixtures_parent_class_is_assignable_from_fixture() {
    $this->assertTrue(XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')->isAssignableFrom($this->fixture));
  }

  #[@test]
  public function this_class_is_not_assignable_from_fixture() {
    $this->assertFalse($this->getClass()->isAssignableFrom($this->fixture));
  }

  #[@test, @values([
  #  'int', 'double', 'string', 'bool',
  #  Primitive::$INT, Primitive::$DOUBLE, Primitive::$STRING, Primitive::$BOOL
  #])]
  public function fixture_is_not_assignable_from_primitive($name) {
    $this->assertFalse($this->fixture->isAssignableFrom($name));
  }

  #[@test, @expect('lang.IllegalStateException')]
  public function illegal_argument_given_to_isAssignableFrom() {
    $this->fixture->isAssignableFrom('@not-a-type@');
  }

  #[@test]
  public function fixtures_parent_class() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass'),
      $this->fixture->getParentClass()
    );
  }

  #[@test]
  public function fixtures_parents_parent_class() {
    $this->assertEquals(
      XPClass::forName('lang.Object'),
      $this->fixture->getParentClass()->getParentClass()
    );
  }

  #[@test]
  public function object_classes_parent_is_null() {
    $this->assertNull(XPClass::forName('lang.Object')->getParentClass());
  }

  #[@test]
  public function fixture_class_is_not_an_interface() {
    $this->assertFalse($this->fixture->isInterface());
  }

  #[@test]
  public function lang_Generic_class_is_an_interface() {
    $this->assertTrue(XPClass::forName('lang.Generic')->isInterface());
  }

  #[@test]
  public function getInterfaces_returns_array_of_class() {
    $this->assertInstanceOf('lang.XPClass[]', $this->fixture->getInterfaces());
  }

  #[@test]
  public function getInterfaces_contains_declared_interface() {
    $this->assertTrue(in_array(
      XPClass::forName('util.log.Traceable'),
      $this->fixture->getInterfaces()
    ));
  }

  #[@test]
  public function getDeclaredInterfaces_consist_of_declared_interface() {
    $this->assertEquals(
      array(XPClass::forName('util.log.Traceable')), 
      $this->fixture->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function object_class_has_lang_Generic_interface() {
    $this->assertEquals(
      array(XPClass::forName('lang.Generic')), 
      XPClass::forName('lang.Object')->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function this_class_does_not_declare_any_interfaces() {
    $this->assertEquals(array(), $this->getClass()->getDeclaredInterfaces());
  }

  #[@test]
  public function util_collections_IList_class_declares_ArrayAccess_and_IteratorAggregate_interfaces() {
    $this->assertEquals(
      array(new XPClass('ArrayAccess'), new XPClass('IteratorAggregate')), 
      XPClass::forName('util.collections.IList')->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function fixture_class_has_a_constructor() {
    $this->assertTrue($this->fixture->hasConstructor());
  }

  #[@test]
  public function fixture_classes_constructor() {
    $this->assertInstanceOf('lang.reflect.Constructor', $this->fixture->getConstructor());
  }

  #[@test]
  public function object_class_does_not_have_a_constructor() {
    $this->assertFalse(XPClass::forName('lang.Object')->hasConstructor());
  }

  #[@test, @expect('lang.ElementNotFoundException')]
  public function getting_object_classes_constructor_raises_an_exception() {
    XPClass::forName('lang.Object')->getConstructor();
  }

  #[@test]
  public function invoking_fixture_classes_constructor() {
    $this->assertEquals(
      new TestClass('1977-12-14'),
      $this->fixture->getConstructor()->newInstance(array('1977-12-14'))
    );
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function newInstance_raises_exception_if_class_is_an_interface() {
    XPClass::forName('util.log.Traceable')->newInstance();
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function newInstance_raises_exception_if_class_is_abstract() {
    XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')->newInstance();
  }

  #[@test, @expect('lang.reflect.TargetInvocationException')]
  public function constructors_newInstance_method_wraps_exceptions() {
    $this->fixture->getConstructor()->newInstance(array('@@not-a-valid-date-string@@'));
  }

  #[@test, @expect('lang.IllegalAccessException')]
  public function constructors_newInstance_method_raises_exception_if_class_is_abstract() {
    XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')
      ->getConstructor()
      ->newInstance()
    ;
  }
  
  #[@test]
  public function implementedConstructorInvocation() {
    $parent= 'net.xp_framework.unittest.reflection.AbstractTestClass';
    $i= \lang\ClassLoader::defineClass('ANonAbstractClass', $parent, array(), '{
      public function getDate() {}
    }');    
    $this->assertInstanceOf($parent, $i->getConstructor()->newInstance());
  }

  #[@test]
  public function fixture_class_has_annotations() {
    $this->assertTrue($this->fixture->hasAnnotations());
  }

  #[@test]
  public function fixture_class_annotations() {
    $this->assertEquals(array('test' => 'Annotation'), $this->fixture->getAnnotations());
  }

  #[@test]
  public function fixture_class_has_test_annotation() {
    $this->assertTrue($this->fixture->hasAnnotation('test'));
  }

  #[@test]
  public function fixture_class_test_annotation() {
    $this->assertEquals('Annotation', $this->fixture->getAnnotation('test'));
  }
  
  #[@test, @expect('lang.ElementNotFoundException')]
  public function getting_non_existant_annotation_raises_exception() {
    $this->fixture->getAnnotation('non-existant');
  }
  
  #[@test, @expect('lang.ClassNotFoundException')]
  public function forName_raises_exceptions_for_nonexistant_classes() {
    XPClass::forName('class.does.not.Exist');
  }

  #[@test]
  public function getClasses_returns_a_list_of_class_objects() {
    $this->assertInstanceOf('lang.XPClass[]', XPClass::getClasses());
  }
  
  #[@test]
  public function fixture_class_constants() {
    $this->assertEquals(
      array('CONSTANT_STRING' => 'XP Framework', 'CONSTANT_INT' => 15, 'CONSTANT_NULL' => null),
      $this->fixture->getConstants()
    );
  }

  #[@test, @values(['CONSTANT_STRING', 'CONSTANT_INT', 'CONSTANT_NULL'])]
  public function hasConstant_returns_true_for_existing_constant($name) {
    $this->assertTrue($this->fixture->hasConstant($name));
  }

  #[@test, @values(['DOES_NOT_EXIST', '', null])]
  public function hasConstant_returns_false_for_non_existing_constant($name) {
    $this->assertFalse($this->fixture->hasConstant($name));
  }

  #[@test, @values([
  #  ['XP Framework', 'CONSTANT_STRING'],
  #  [15, 'CONSTANT_INT'],
  #  [null, 'CONSTANT_NULL']
  #])]
  public function getConstant_returns_constants_value($value, $name) {
    $this->assertEquals('XP Framework', $this->fixture->getConstant('CONSTANT_STRING'));
  }

  #[@test, @expect('lang.ElementNotFoundException'), @values(['DOES_NOT_EXIST', '', null])]
  public function getConstant_throws_exception_if_constant_doesnt_exist($name) {
    $this->fixture->getConstant($name);
  }
}
