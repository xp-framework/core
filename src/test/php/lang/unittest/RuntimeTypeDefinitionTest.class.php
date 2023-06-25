<?php namespace lang\unittest;

use lang\{ClassLoader, DynamicClassLoader, XPClass};
use unittest\{Assert, Test, Values};

abstract class RuntimeTypeDefinitionTest {

  /**
   * Wraps around a function which defines types, giving it unique names and
   * verifying the type has not been defined before.
   *
   * @param  string $annotations
   * @param  string $name Uses a unique name if NULL is passed
   * @param  var $define A function
   * @return lang.XPClass
   * @throws unittest.AssertionFailedError
   */
  protected function defineType($annotations, $name, $define) {
    static $uniq= 0;

    $t= $name ?: nameof($this).'__'.($uniq++);
    $spec= trim($annotations.' '.$t);
    if (interface_exists(literal($t), false) || class_exists(literal($t), false)) {
      $this->fail('Type may not exist!', $t, null);
    }
    return $define($spec);
  }

  /**
   * Define a type
   *
   * @param   [:var] $decl
   * @param   string $def
   * @return  lang.XPClass
   */
  protected abstract function define(array $decl= [], $def= null);

  #[Test]
  public function returns_XPClass_instances() {
    Assert::instance(XPClass::class, $this->define());
  }

  #[Test]
  public function classloader_of_defined_type_is_DynamicClassLoader() {
    Assert::instance(DynamicClassLoader::class, $this->define()->getClassLoader());
  }

  #[Test]
  public function package_name() {
    Assert::equals('lang.unittest', $this->define()->getPackage()->getName());
  }

  #[Test]
  public function default_classloader_provides_defined_type() {
    Assert::true(ClassLoader::getDefault()->providesClass($this->define()->getName()));
  }

  #[Test]
  public function default_classloader_provides_packaged_of_defined_type() {
    Assert::true(ClassLoader::getDefault()->providesPackage($this->define()->getPackage()->getName()));
  }

  #[Test]
  public function declares_passed_annotation() {
    Assert::true($this->define(['annotations' => '#[Test]'])->hasAnnotation('test'));
  }

  #[Test]
  public function declares_passed_annotation_with_value() {
    Assert::equals('/rest', $this->define(['annotations' => '#[Webservice(["path" => "/rest"])]'])->getAnnotation('webservice', 'path'));
  }

  #[Test, Values(['com.example.test.RTTDDotted', 'com\\example\\test\\RTTDNative'])]
  public function type_with_package_is_declared_inside_namespace($name) {
    $name.= typeof($this)->getSimpleName().$name;
    Assert::equals('com\\example\\test\\', substr($this->define(['name' => $name])->literal(), 0, 17));
  }

  #[Test]
  public function type_without_package_is_declared_globally() {
    $name= typeof($this)->getSimpleName().'RTTDGlobal';
    Assert::equals($name, $this->define(['name' => $name])->literal());
  }
}