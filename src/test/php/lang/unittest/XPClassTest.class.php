<?php namespace lang\unittest;

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
  public function declared_name() {
    Assert::equals('TestClass', $this->fixture->declaredName());
  }

  #[Test]
  public function package_name() {
    Assert::equals('lang.unittest', $this->fixture->packageName());
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
    Assert::instance('lang.XPClass[]', [...XPClass::getClasses()]);
  }
}