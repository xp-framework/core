<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\{Constructor, Package, TargetInvocationException};
use lang\{
  ClassLoader,
  ClassNotFoundException,
  ElementNotFoundException,
  IllegalAccessException,
  IllegalStateException,
  Primitive,
  XPClass
};
use unittest\{Expect, Test, Values};

/**
 * Test the XPClass class, the entry point to the XP Framework's class reflection API.
 *
 * @see  xp://lang.XPClass
 */
class XPClassTest extends \unittest\TestCase {
  private $fixture;

  /** @return void */
  public function setUp() {
    $this->fixture= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
  }
 
  #[Test]
  public function getName_returns_fully_qualified_name() {
    $this->assertEquals('net.xp_framework.unittest.reflection.TestClass', $this->fixture->getName());
  }

  #[Test]
  public function literal_returns_name_as_known_to_PHP() {
    $this->assertEquals(TestClass::class, $this->fixture->literal());
  }

  #[Test]
  public function getSimpleName_returns_class_name_only() {
    $this->assertEquals('TestClass', $this->fixture->getSimpleName());
  }

  #[Test]
  public function getPackage_returns_package_class_resides_in() {
    $this->assertEquals(
      Package::forName('net.xp_framework.unittest.reflection'),
      $this->fixture->getPackage()
    );
  }

  #[Test]
  public function newInstance_creates_instances_of_class() {
    $this->assertEquals(new TestClass(1), $this->fixture->newInstance(1));
  }

  #[Test]
  public function instance_created_with_new_is_instance_of_class() {
    $this->assertTrue($this->fixture->isInstance(new TestClass(1)));
  }
  
  #[Test]
  public function is_subclass_of_its_parent() {
    $this->assertTrue($this->fixture->isSubclassOf('net.xp_framework.unittest.reflection.AbstractTestClass'));
  }

  #[Test]
  public function is_not_subclass_of_util_Date() {
    $this->assertFalse($this->fixture->isSubclassOf('util.Date'));
  }

  #[Test]
  public function is_not_subclass_of_itself() {
    $this->assertFalse($this->fixture->isSubclassOf('net.xp_framework.unittest.reflection.TestClass'));
  }

  #[Test]
  public function class_is_assignable_from_itself() {
    $this->assertTrue($this->fixture->isAssignableFrom($this->fixture));
  }

  #[Test]
  public function fixtures_parent_class_is_assignable_from_fixture() {
    $this->assertTrue(XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass')->isAssignableFrom($this->fixture));
  }

  #[Test]
  public function this_class_is_not_assignable_from_fixture() {
    $this->assertFalse(typeof($this)->isAssignableFrom($this->fixture));
  }

  #[Test, Values(['int', 'double', 'string', 'bool', Primitive::$INT, Primitive::$FLOAT, Primitive::$STRING, Primitive::$BOOL])]
  public function fixture_is_not_assignable_from_primitive($name) {
    $this->assertFalse($this->fixture->isAssignableFrom($name));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function illegal_argument_given_to_isAssignableFrom() {
    $this->fixture->isAssignableFrom('@not-a-type@');
  }

  #[Test]
  public function fixtures_parent_class() {
    $this->assertEquals(
      XPClass::forName('net.xp_framework.unittest.reflection.AbstractTestClass'),
      $this->fixture->getParentClass()
    );
  }

  #[Test]
  public function fixtures_parents_parent_class() {
    $this->assertNull($this->fixture->getParentClass()->getParentClass());
  }

  #[Test]
  public function fixture_class_is_not_an_interface() {
    $this->assertFalse($this->fixture->isInterface());
  }

  #[Test]
  public function lang_Value_class_is_an_interface() {
    $this->assertTrue(XPClass::forName('lang.Value')->isInterface());
  }

  #[Test]
  public function fixture_class_is_not_a_trait() {
    $this->assertFalse($this->fixture->isTrait());
  }

  #[Test]
  public function lang_Value_class_is_not_a_trait() {
    $this->assertFalse(XPClass::forName('lang.Value')->isTrait());
  }

  #[Test]
  public function trait_class_is_trait() {
    $this->assertTrue(XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')->isTrait());
  }

  #[Test]
  public function traits_of_fixture() {
    $this->assertEquals(
      [],
      $this->fixture->getTraits()
    );
  }

  #[Test]
  public function traits_of_UsingOne() {
    $this->assertEquals(
      [XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')],
      XPClass::forName('net.xp_framework.unittest.reflection.classes.UsingOne')->getTraits()
    );
  }

  #[Test]
  public function traits_of_TraitOne() {
    $this->assertEquals(
      [],
      XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')->getTraits()
    );
  }

  #[Test]
  public function getInterfaces_returns_array_of_class() {
    $this->assertInstanceOf('lang.XPClass[]', $this->fixture->getInterfaces());
  }

  #[Test]
  public function getInterfaces_consist_of_declared_interface() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable')],
      $this->fixture->getInterfaces()
    );
  }

  #[Test]
  public function getDeclaredInterfaces_consist_of_declared_interface() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable')],
      $this->fixture->getDeclaredInterfaces()
    );
  }

  #[Test]
  public function this_class_does_not_declare_any_interfaces() {
    $this->assertEquals([], typeof($this)->getDeclaredInterfaces());
  }

  #[Test]
  public function fixture_class_has_a_constructor() {
    $this->assertTrue($this->fixture->hasConstructor());
  }

  #[Test]
  public function fixture_classes_constructor() {
    $this->assertInstanceOf(Constructor::class, $this->fixture->getConstructor());
  }

  #[Test]
  public function value_class_does_not_have_a_constructor() {
    $this->assertFalse(XPClass::forName('lang.Value')->hasConstructor());
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function getting_value_classes_constructor_raises_an_exception() {
    XPClass::forName('lang.Value')->getConstructor();
  }

  #[Test]
  public function invoking_fixture_classes_constructor() {
    $this->assertEquals(
      new TestClass('1977-12-14'),
      $this->fixture->getConstructor()->newInstance(['1977-12-14'])
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_an_interface() {
    XPClass::forName('lang.Runnable')->newInstance();
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_a_trait() {
    XPClass::forName('net.xp_framework.unittest.reflection.classes.TraitOne')->newInstance();
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_abstract() {
    XPClass::forName(AbstractTestClass::class)->newInstance();
  }

  #[Test, Expect(TargetInvocationException::class)]
  public function constructors_newInstance_method_wraps_exceptions() {
    $this->fixture->getConstructor()->newInstance(['@@not-a-valid-date-string@@']);
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function constructors_newInstance_method_raises_exception_if_class_is_abstract() {
    XPClass::forName(AbstractTestClass::class)->getConstructor()->newInstance();
  }
  
  #[Test]
  public function implementedConstructorInvocation() {
    $i= ClassLoader::defineClass('ANonAbstractClass', AbstractTestClass::class, [], '{
      public function getDate() {}
    }');    
    $this->assertInstanceOf(AbstractTestClass::class, $i->getConstructor()->newInstance());
  }

  #[Test]
  public function fixture_class_has_annotations() {
    $this->assertTrue($this->fixture->hasAnnotations());
  }

  #[Test]
  public function fixture_class_annotations() {
    $this->assertEquals(['test' => 'Annotation'], $this->fixture->getAnnotations());
  }

  #[Test]
  public function fixture_class_has_test_annotation() {
    $this->assertTrue($this->fixture->hasAnnotation('test'));
  }

  #[Test]
  public function fixture_class_test_annotation() {
    $this->assertEquals('Annotation', $this->fixture->getAnnotation('test'));
  }
  
  #[Test, Expect(ElementNotFoundException::class)]
  public function getting_non_existant_annotation_raises_exception() {
    $this->fixture->getAnnotation('non-existant');
  }
  
  #[Test, Expect(ClassNotFoundException::class)]
  public function forName_raises_exceptions_for_nonexistant_classes() {
    XPClass::forName('class.does.not.Exist');
  }

  #[Test]
  public function forName_supports_class_literals() {
    $this->assertEquals($this->fixture, XPClass::forName(TestClass::class));
  }

  #[Test]
  public function forName_supports_absolute_class_names() {
    $this->assertEquals($this->fixture, XPClass::forName('\\net\\xp_framework\\unittest\\reflection\\TestClass'));
  }

  #[Test]
  public function forName_supports_native_classes() {
    $this->assertEquals(new XPClass(\Exception::class), XPClass::forName(\Exception::class));
  }

  #[Test]
  public function getClasses_returns_a_list_of_class_objects() {
    $this->assertInstanceOf('lang.XPClass[]', iterator_to_array(XPClass::getClasses()));
  }
  
  #[Test]
  public function fixture_class_constants() {
    $this->assertEquals(
      ['CONSTANT_STRING' => 'XP Framework', 'CONSTANT_INT' => 15, 'CONSTANT_NULL' => null],
      $this->fixture->getConstants()
    );
  }

  #[Test, Values(['CONSTANT_STRING', 'CONSTANT_INT', 'CONSTANT_NULL'])]
  public function hasConstant_returns_true_for_existing_constant($name) {
    $this->assertTrue($this->fixture->hasConstant($name));
  }

  #[Test, Values(['DOES_NOT_EXIST', '', null])]
  public function hasConstant_returns_false_for_non_existing_constant($name) {
    $this->assertFalse($this->fixture->hasConstant($name));
  }

  #[Test, Values([['XP Framework', 'CONSTANT_STRING'], [15, 'CONSTANT_INT'], [null, 'CONSTANT_NULL']])]
  public function getConstant_returns_constants_value($value, $name) {
    $this->assertEquals('XP Framework', $this->fixture->getConstant('CONSTANT_STRING'));
  }

  #[Test, Expect(ElementNotFoundException::class), Values(['DOES_NOT_EXIST', '', null])]
  public function getConstant_throws_exception_if_constant_doesnt_exist($name) {
    $this->fixture->getConstant($name);
  }
}