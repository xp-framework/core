<?php namespace lang\unittest;

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
use test\{Assert, Before, Expect, Test, Values};

class XPClassTest {
  private $fixture;

  #[Before]
  public function setUp() {
    $this->fixture= XPClass::forName('lang.unittest.TestClass');
  }
 
  #[Test]
  public function getName_returns_fully_qualified_name() {
    Assert::equals('lang.unittest.TestClass', $this->fixture->getName());
  }

  #[Test]
  public function literal_returns_name_as_known_to_PHP() {
    Assert::equals(TestClass::class, $this->fixture->literal());
  }

  #[Test]
  public function getSimpleName_returns_class_name_only() {
    Assert::equals('TestClass', $this->fixture->getSimpleName());
  }

  #[Test]
  public function getPackage_returns_package_class_resides_in() {
    Assert::equals(
      Package::forName('lang.unittest'),
      $this->fixture->getPackage()
    );
  }

  #[Test]
  public function newInstance_creates_instances_of_class() {
    Assert::equals(new TestClass(1), $this->fixture->newInstance(1));
  }

  #[Test]
  public function instance_created_with_new_is_instance_of_class() {
    Assert::true($this->fixture->isInstance(new TestClass(1)));
  }
  
  #[Test]
  public function is_subclass_of_its_parent() {
    Assert::true($this->fixture->isSubclassOf('lang.unittest.AbstractTestClass'));
  }

  #[Test]
  public function is_not_subclass_of_util_Date() {
    Assert::false($this->fixture->isSubclassOf('util.Date'));
  }

  #[Test]
  public function is_not_subclass_of_itself() {
    Assert::false($this->fixture->isSubclassOf('lang.unittest.TestClass'));
  }

  #[Test]
  public function class_is_assignable_from_itself() {
    Assert::true($this->fixture->isAssignableFrom($this->fixture));
  }

  #[Test]
  public function fixtures_parent_class_is_assignable_from_fixture() {
    Assert::true(XPClass::forName('lang.unittest.AbstractTestClass')->isAssignableFrom($this->fixture));
  }

  #[Test]
  public function this_class_is_not_assignable_from_fixture() {
    Assert::false(typeof($this)->isAssignableFrom($this->fixture));
  }

  #[Test, Values(['int', 'double', 'string', 'bool'])]
  public function fixture_is_not_assignable_from_primitive_name($name) {
    Assert::false($this->fixture->isAssignableFrom($name));
  }

  #[Test, Values(['int', 'double', 'string', 'bool'])]
  public function fixture_is_not_assignable_from_primitive_object($name) {
    Assert::false($this->fixture->isAssignableFrom(Primitive::forName($name)));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function illegal_argument_given_to_isAssignableFrom() {
    $this->fixture->isAssignableFrom('@not-a-type@');
  }

  #[Test]
  public function fixtures_parent_class() {
    Assert::equals(
      XPClass::forName('lang.unittest.AbstractTestClass'),
      $this->fixture->getParentClass()
    );
  }

  #[Test]
  public function fixtures_parents_parent_class() {
    Assert::null($this->fixture->getParentClass()->getParentClass());
  }

  #[Test]
  public function fixture_class_is_not_an_interface() {
    Assert::false($this->fixture->isInterface());
  }

  #[Test]
  public function lang_Value_class_is_an_interface() {
    Assert::true(XPClass::forName('lang.Value')->isInterface());
  }

  #[Test]
  public function fixture_class_is_not_a_trait() {
    Assert::false($this->fixture->isTrait());
  }

  #[Test]
  public function lang_Value_class_is_not_a_trait() {
    Assert::false(XPClass::forName('lang.Value')->isTrait());
  }

  #[Test]
  public function trait_class_is_trait() {
    Assert::true(XPClass::forName('lang.unittest.fixture.TraitOne')->isTrait());
  }

  #[Test]
  public function traits_of_fixture() {
    Assert::equals(
      [],
      $this->fixture->getTraits()
    );
  }

  #[Test]
  public function traits_of_UsingOne() {
    Assert::equals(
      [XPClass::forName('lang.unittest.fixture.TraitOne')],
      XPClass::forName('lang.unittest.fixture.UsingOne')->getTraits()
    );
  }

  #[Test]
  public function traits_of_TraitOne() {
    Assert::equals(
      [],
      XPClass::forName('lang.unittest.fixture.TraitOne')->getTraits()
    );
  }

  #[Test]
  public function getInterfaces_returns_array_of_class() {
    Assert::instance('lang.XPClass[]', $this->fixture->getInterfaces());
  }

  #[Test]
  public function getInterfaces_consist_of_declared_interface() {
    Assert::equals(
      [XPClass::forName('lang.Runnable')],
      $this->fixture->getInterfaces()
    );
  }

  #[Test]
  public function getDeclaredInterfaces_consist_of_declared_interface() {
    Assert::equals(
      [XPClass::forName('lang.Runnable')],
      $this->fixture->getDeclaredInterfaces()
    );
  }

  #[Test]
  public function this_class_does_not_declare_any_interfaces() {
    Assert::equals([], typeof($this)->getDeclaredInterfaces());
  }

  #[Test]
  public function invoking_fixture_classes_constructor() {
    Assert::equals(
      new TestClass('1977-12-14'),
      $this->fixture->newInstance('1977-12-14')
    );
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_an_interface() {
    XPClass::forName('lang.Runnable')->newInstance();
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_a_trait() {
    XPClass::forName('lang.unittest.fixture.TraitOne')->newInstance();
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function newInstance_raises_exception_if_class_is_abstract() {
    XPClass::forName(AbstractTestClass::class)->newInstance();
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function forName_raises_exceptions_for_nonexistant_classes() {
    XPClass::forName('class.does.not.Exist');
  }

  #[Test]
  public function forName_supports_class_literals() {
    Assert::equals($this->fixture, XPClass::forName(TestClass::class));
  }

  #[Test]
  public function forName_supports_absolute_class_names() {
    Assert::equals($this->fixture, XPClass::forName('\\lang\unittest\\TestClass'));
  }

  #[Test]
  public function forName_supports_native_classes() {
    Assert::equals(new XPClass(\Exception::class), XPClass::forName(\Exception::class));
  }

  #[Test]
  public function getClasses_returns_a_list_of_class_objects() {
    Assert::instance('lang.XPClass[]', iterator_to_array(XPClass::getClasses()));
  }
}