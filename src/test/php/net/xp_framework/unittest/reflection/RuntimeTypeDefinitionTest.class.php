<?php namespace net\xp_framework\unittest\reflection;

use lang\ClassLoader;
use lang\DynamicClassLoader;
use lang\XPClass;

/**
 * Base class for runtime type definitions
 *
 * @see   xp://lang.ClassLoader
 * @see   https://github.com/xp-framework/xp-framework/issues/94
 */
abstract class RuntimeTypeDefinitionTest extends \unittest\TestCase {

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
    $t= $name ?: nameof($this).'__'.$this->name;
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

  #[@test]
  public function returns_XPClass_instances() {
    $this->assertInstanceOf(XPClass::class, $this->define());
  }

  #[@test]
  public function classloader_of_defined_type_is_DynamicClassLoader() {
    $this->assertInstanceOf(DynamicClassLoader::class, $this->define()->getClassLoader());
  }

  #[@test]
  public function package_name() {
    $this->assertEquals('net.xp_framework.unittest.reflection', $this->define()->getPackage()->getName());
  }

  #[@test]
  public function default_classloader_provides_defined_type() {
    $this->assertTrue(ClassLoader::getDefault()->providesClass($this->define()->getName()));
  }

  #[@test]
  public function default_classloader_provides_packaged_of_defined_type() {
    $this->assertTrue(ClassLoader::getDefault()->providesPackage($this->define()->getPackage()->getName()));
  }

  #[@test]
  public function declares_passed_annotation() {
    $this->assertTrue($this->define(['annotations' => '#[@test]'])->hasAnnotation('test'));
  }

  #[@test]
  public function declares_passed_annotation_with_value() {
    $this->assertEquals('/rest', $this->define(['annotations' => '#[@webservice(path= "/rest")]'])->getAnnotation('webservice', 'path'));
  }

  #[@test, @values(['com.example.test.RTTDDotted', 'com\\example\\test\\RTTDNative'])]
  public function type_with_package_is_declared_inside_namespace($name) {
    $name.= typeof($this)->getSimpleName().$name;
    $this->assertEquals('com\\example\\test\\', substr($this->define(['name' => $name])->literal(), 0, 17));
  }

  #[@test]
  public function type_without_package_is_declared_globally() {
    $name= typeof($this)->getSimpleName().'RTTDGlobal';
    $this->assertEquals($name, $this->define(['name' => $name])->literal());
  }
}
