<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Throwable;
use lang\Runnable;
use lang\ClassLoader;
use lang\ClassNotFoundException;

/**
 * TestCase for lang.ClassLoader::defineClass()
 */
class RuntimeClassDefinitionTest extends RuntimeTypeDefinitionTest {

  /**
   * This `define()` implementation creates classes
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
        return ClassLoader::defineClass(
          $spec,
          array_key_exists('parent', $decl) ? $decl['parent'] : null,
          array_key_exists('interfaces', $decl) ? $decl['interfaces'] : [],
          $def
        );
      }
    );
  }

  #[@test]
  public function given_parent_is_inherited() {
    $this->assertTrue($this->define(['parent' => Throwable::class])->isSubclassOf(Throwable::class));
  }

  #[@test]
  public function given_parent_class_is_inherited() {
    $this->assertTrue($this->define(['parent' => XPClass::forName(Throwable::class)])->isSubclassOf(Throwable::class));
  }

  #[@test]
  public function given_interface_is_implemented() {
    $class= $this->define(['interfaces' => [Runnable::class]], '{
      public function run() { } 
    }');

    $this->assertTrue($class->isSubclassOf(Runnable::class));
  }

  #[@test]
  public function given_interface_class_is_implemented() {
    $class= $this->define(['interfaces' => [XPClass::forName(Runnable::class)]], '{
      public function run() { } 
    }');

    $this->assertTrue($class->isSubclassOf(Runnable::class));
  }

  #[@test]
  public function field_exists() {
    $class= $this->define([], '{ public $fixture= null; }');
    $this->assertTrue($class->hasField('fixture'));
  }

  #[@test]
  public function method_exists() {
    $class= $this->define([], '{ public function fixture() { } }');
    $this->assertTrue($class->hasMethod('fixture'));
  }

  #[@test]
  public function parents_method_exists() {
    $this->assertTrue($this->define(['parent' => Throwable::class])->hasMethod('toString'));
  }

  #[@test]
  public function parents_field_exists() {
    $this->assertTrue($this->define(['parent' => Throwable::class])->hasField('message'));
  }

  #[@test]
  public function static_initializer_is_invoked() {
    $class= $this->define([], '{
      public static $initializerCalled= false;
      static function __static() { self::$initializerCalled= true; }
    }');
    $this->assertTrue($class->getField('initializerCalled')->get(null));
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function cannot_define_class_with_non_existant_parent() {
    $this->define(['parent' => '@@nonexistant@@']);
  }

  #[@test, @expect(ClassNotFoundException::class), @values([
  #  [['@@nonexistant@@']],
  #  [[Runnable::class, '@@nonexistant@@']],
  #  [['@@nonexistant@@', Runnable::class]]
  #])]
  public function cannot_define_class_with_non_existant_interface($list) {
    $this->define(['interfaces' => $list]);
  }

  #[@test, @expect(ClassNotFoundException::class)]
  public function cannot_define_class_with_null_interface() {
    $this->define(['interfaces' => [null]]);
  }

  #[@test]
  public function closure_map_style_declaring_field() {
    $class= $this->define([], ['fixture' => null]);
    $this->assertTrue($class->hasField('fixture'));
  }

  #[@test]
  public function closure_map_style_declaring_method() {
    $class= $this->define([], ['fixture' => function() { }]);
    $this->assertTrue($class->hasMethod('fixture'));
  }

  #[@test]
  public function closure_map_field_access() {
    $class= $this->define([], ['fixture' => 'Test']);
    $instance= $class->newInstance();
    $this->assertEquals('Test', $class->getField('fixture')->get($instance));
  }

  #[@test]
  public function closure_map_method_invocation() {
    $class= $this->define([], ['fixture' => function($a, $b) { return [$this, $a, $b]; }]);
    $instance= $class->newInstance();
    $this->assertEquals([$instance, 1, 2], $class->getMethod('fixture')->invoke($instance, [1, 2]));
  }
}
