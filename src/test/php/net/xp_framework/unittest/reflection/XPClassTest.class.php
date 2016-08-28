<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Primitive;
use lang\ClassLoader;
use lang\IllegalStateException;
use lang\ElementNotFoundException;
use lang\ClassNotFoundException;
use lang\IllegalAccessException;
use lang\reflect\Package;
use lang\reflect\Constructor;
use lang\reflect\TargetInvocationException;

/**
 * Test the XPClass class, the entry point to the XP Framework's class reflection API.
 *
 * @see  xp://lang.XPClass
 */
class XPClassTest extends \unittest\TestCase {
  protected $fixture;

  /** @return void */
  public function setUp() {
    $this->fixture= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
  }
 
  #[@test]
  public function getName_returns_fully_qualified_name() {
    $this->assertEquals('net.xp_framework.unittest.reflection.TestClass', $this->fixture->getName());
  }

  #[@test]
  public function literal_returns_name_as_known_to_PHP() {
    $this->assertEquals(TestClass::class, $this->fixture->literal());
  }

  #[@test]
  public function getSimpleName_returns_class_name_only() {
    $this->assertEquals('TestClass', $this->fixture->getSimpleName());
  }

  #[@test]
  public function getPackage_returns_package_class_resides_in() {
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.reflection'),
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

  #[@test, @expect(ClassNotFoundException::class)]
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
  public function fixture_class_is_not_a_trait() {
    $this->assertFalse($this->fixture->isTrait());
  }

  #[@test]
  public function lang_Generic_class_is_not_a_trait() {
    $this->assertFalse(XPClass::forName('lang.Generic')->isTrait());
  }

  #[@test]
  public function trait_class_is_trait() {
    $this->assertTrue(XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')->isTrait());
  }

  #[@test]
  public function traits_of_fixture() {
    $this->assertEquals(
      [],
      $this->fixture->getTraits()
    );
  }

  #[@test]
  public function traits_of_UsingOne() {
    $this->assertEquals(
      [XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')],
      XPClass::forName('net.xp_framework.unittest.reflection.classes.UsingOne')->getTraits()
    );
  }

  #[@test]
  public function traits_of_TraitOne() {
    $this->assertEquals(
      [],
      XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')->getTraits()
    );
  }

  #[@test]
  public function getInterfaces_returns_array_of_class() {
    $this->assertInstanceOf('lang.XPClass[]', $this->fixture->getInterfaces());
  }

  #[@test]
  public function getInterfaces_contains_declared_interface() {
    $this->assertTrue(in_array(
      XPClass::forName('lang.Runnable'),
      $this->fixture->getInterfaces()
    ));
  }

  #[@test]
  public function getDeclaredInterfaces_consist_of_declared_interface() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable')],
      $this->fixture->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function object_class_has_lang_Generic_interface() {
    $this->assertEquals(
      [XPClass::forName('lang.Generic')],
      XPClass::forName('lang.Object')->getDeclaredInterfaces()
    );
  }

  #[@test]
  public function this_class_does_not_declare_any_interfaces() {
    $this->assertEquals([], $this->getClass()->getDeclaredInterfaces());
  }

  #[@test]
  public function fixture_class_has_a_constructor() {
    $this->assertTrue($this->fixture->hasConstructor());
  }

  #[@test]
  public function fixture_classes_constructor() {
    $this->assertInstanceOf(Constructor::class, $this->fixture->getConstructor());
  }

  #[@test]
  public function object_class_does_not_have_a_constructor() {
    $this->assertFalse(XPClass::forName('lang.Object')->hasConstructor());
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function getting_object_classes_constructor_raises_an_exception() {
    XPClass::forName('lang.Object')->getConstructor();
  }

  #[@test]
  public function invoking_fixture_classes_constructor() {
    $this->assertEquals(
      new TestClass('1977-12-14'),
      $this->fixture->getConstructor()->newInstance(['1977-12-14'])
    );
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_an_interface() {
    XPClass::forName('lang.Runnable')->newInstance();
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_a_trait() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')->newInstance();
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_abstract() {
    XPClass::forName(AbstractTestClass::class)->newInstance();
  }

  #[@test, @expect(TargetInvocationException::class)]
  public function constructors_newInstance_method_wraps_exceptions() {
    $this->fixture->getConstructor()->newInstance(['@@not-a-valid-date-string@@']);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function constructors_newInstance_method_raises_exception_if_class_is_abstract() {
    XPClass::forName(AbstractTestClass::class)->getConstructor()->newInstance();
  }
  
  #[@test]
  public function implementedConstructorInvocation() {
    $i= ClassLoader::defineClass('ANonAbstractClass', AbstractTestClass::class, [], '{
      public function getDate() {}
    }');    
    $this->assertInstanceOf(AbstractTestClass::class, $i->getConstructor()->newInstance());
  }

  #[@test]
  public function fixture_class_has_annotations() {
    $this->assertTrue($this->fixture->hasAnnotations());
  }

  #[@test]
  public function fixture_class_annotations() {
    $this->assertEquals(['test' => 'Annotation'], $this->fixture->getAnnotations());
  }

  #[@test]
  public function fixture_class_has_test_annotation() {
    $this->assertTrue($this->fixture->hasAnnotation('test'));
  }

  #[@test]
  public function fixture_class_test_annotation() {
    $this->assertEquals('Annotation', $this->fixture->getAnnotation('test'));
  }
  
  #[@test, @expect(ElementNotFoundException::class)]
  public function getting_non_existant_annotation_raises_exception() {
    $this->fixture->getAnnotation('non-existant');
  }
  
  #[@test, @expect(ClassNotFoundException::class)]
  public function forName_raises_exceptions_for_nonexistant_classes() {
    XPClass::forName('class.does.not.Exist');
  }

  #[@test]
  public function forName_supports_class_literals() {
    $this->assertEquals($this->fixture, XPClass::forName(TestClass::class));
  }

  #[@test]
  public function forName_supports_absolute_class_names() {
    $this->assertEquals($this->fixture, XPClass::forName('\\net\\xp_framework\\unittest\\reflection\\TestClass'));
  }

  #[@test]
  public function forName_supports_native_classes() {
    $this->assertEquals(new XPClass(\Exception::class), XPClass::forName(\Exception::class));
  }

  #[@test]
  public function getClasses_returns_a_list_of_class_objects() {
    $this->assertInstanceOf('lang.XPClass[]', iterator_to_array(XPClass::getClasses()));
  }
  
  #[@test]
  public function fixture_class_constants() {
    $this->assertEquals(
      ['CONSTANT_STRING' => 'XP Framework', 'CONSTANT_INT' => 15, 'CONSTANT_NULL' => null],
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

  #[@test, @expect(ElementNotFoundException::class), @values(['DOES_NOT_EXIST', '', null])]
  public function getConstant_throws_exception_if_constant_doesnt_exist($name) {
    $this->fixture->getConstant($name);
  }
}
