<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Runnable;
use lang\Closeable;
use lang\ClassLoader;
use lang\ClassNotFoundException;

/**
 * TestCase for lang.ClassLoader::defineInterface()
 */
class RuntimeInterfaceDefinitionTest extends RuntimeTypeDefinitionTest {

  /**
   * This `define()` implementation creates interfaces
   *
   * @param   [:var] $decl
   * @param   var $def
   * @return  lang.XPClass
   */
  protected function define(array $decl= [], $def= null) {
    return $this->defineType(
      array_key_exists('annotations', $decl) ? $decl['annotations'] : '',
      array_key_exists('name', $decl) ? $decl['name'] : '',
      function($spec) use($decl, $def) {
        return ClassLoader::defineInterface(
          $spec,
          array_key_exists('parents', $decl) ? $decl['parents'] : [],
          $def
        );
      }
    );
  }

  #[@test]
  public function given_parent_is_inherited() {
    $this->assertEquals(
      [XPClass::forName(Runnable::class)],
      $this->define(['parents' => [Runnable::class]])->getInterfaces()
    );
  }

  #[@test]
  public function given_parent_class_is_inherited() {
    $this->assertEquals(
      [XPClass::forName(Runnable::class)],
      $this->define(['parents' => [XPClass::forName(Runnable::class)]])->getInterfaces()
    );
  }

  #[@test]
  public function given_parents_are_inherited() {
    $this->assertEquals(
      [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)],
      $this->define(['parents' => [Runnable::class, Closeable::class]])->getInterfaces()
    );
  }

  #[@test]
  public function given_parent_classes_are_inherited() {
    $this->assertEquals(
      [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)],
      $this->define(['parents' => [XPClass::forName(Runnable::class), XPClass::forName(Closeable::class)]])->getInterfaces()
    );
  }

  #[@test]
  public function parents_method_exists() {
    $this->assertTrue($this->define(['parents' => [Runnable::class]])->hasMethod('run'));
  }

  #[@test]
  public function method_exists() {
    $class= $this->define(['parents' => [Runnable::class]], '{ public function runAs($user); }');
    $this->assertTrue($class->hasMethod('runAs'));
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function cannot_define_interface_with_non_existant_parent() {
    $this->define(['parents' => ['@@nonexistant@@']]);
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function cannot_define_interface_with_null_parent() {
    $this->define(['parents' => [null]]);
  }

  #[@test]
  public function closure_map_style_declaring_method() {
    $class= $this->define(['parents' => [Runnable::class]], ['fixture' => function() { }]);
    $this->assertTrue($class->hasMethod('fixture'));
  }
}