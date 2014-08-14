<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\ClassLoader;

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
      [XPClass::forName('lang.Runnable')],
      $this->define(['parents' => ['lang.Runnable']])->getInterfaces()
    );
  }

  #[@test]
  public function given_parent_class_is_inherited() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable')],
      $this->define(['parents' => [XPClass::forName('lang.Runnable')]])->getInterfaces()
    );
  }

  #[@test]
  public function given_parents_are_inherited() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable'), XPClass::forName('lang.Closeable')],
      $this->define(['parents' => ['lang.Runnable', 'lang.Closeable']])->getInterfaces()
    );
  }

  #[@test]
  public function given_parent_classes_are_inherited() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable'), XPClass::forName('lang.Closeable')],
      $this->define(['parents' => [XPClass::forName('lang.Runnable'), XPClass::forName('lang.Closeable')]])->getInterfaces()
    );
  }

  #[@test]
  public function parents_method_exists() {
    $this->assertTrue($this->define(['parents' => ['lang.Runnable']])->hasMethod('run'));
  }

  #[@test]
  public function method_exists() {
    $class= $this->define(['parents' => ['lang.Runnable']], '{ public function runAs($user); }');
    $this->assertTrue($class->hasMethod('runAs'));
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_define_interface_with_non_existant_parent() {
    $this->define(['parents' => ['@@nonexistant@@']]);
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_define_interface_with_null_parent() {
    $this->define(['parents' => [null]]);
  }

  #[@test]
  public function closure_map_style_declaring_method() {
    $class= $this->define(['parents' => ['lang.Runnable']], ['fixture' => function() { }]);
    $this->assertTrue($class->hasMethod('fixture'));
  }
}