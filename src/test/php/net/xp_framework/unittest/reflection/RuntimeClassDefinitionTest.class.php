<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\ClassLoader;

/**
 * TestCase for lang.ClassLoader::defineClass()
 */
class RuntimeClassDefinitionTest extends RuntimeTypeDefinitionTest {

  /**
   * This `define()` implementation creates interfaces
   *
   * @param   string $annotations
   * @param   var $parent
   * @param   var[] $interfaces
   * @param   string $bytes
   * @return  lang.XPClass
   */
  protected function define($annotations= '', $parent= 'lang.Object', $interfaces= [], $bytes= '{}') {
    return $this->defineType($annotations, function($spec) use($parent, $interfaces, $bytes) {
      return ClassLoader::defineClass($spec, $parent, $interfaces, $bytes);
    });
  }

  #[@test]
  public function given_parent_is_inherited() {
    $this->assertTrue($this->define('', 'lang.Throwable')->isSubclassOf('lang.Throwable'));
  }

  #[@test]
  public function given_parent_class_is_inherited() {
    $this->assertTrue($this->define('', XPClass::forName('lang.Throwable'))->isSubclassOf('lang.Throwable'));
  }

  #[@test]
  public function given_interface_is_implemented() {
    $class= $this->define('', 'lang.Object', ['lang.Runnable'], '{
      public function run() { } 
    }');

    $this->assertTrue($class->isSubclassOf('lang.Runnable'));
  }

  #[@test]
  public function given_interface_class_is_implemented() {
    $class= $this->define('', 'lang.Object', [XPClass::forName('lang.Runnable')], '{
      public function run() { } 
    }');

    $this->assertTrue($class->isSubclassOf('lang.Runnable'));
  }

  #[@test]
  public function field_exists() {
    $class= $this->define('', 'lang.Object', [], '{ public $fixture= null; }');
    $this->assertTrue($class->hasField('fixture'));
  }

  #[@test]
  public function method_exists() {
    $class= $this->define('', 'lang.Object', [], '{ public function fixture() { } }');
    $this->assertTrue($class->hasMethod('fixture'));
  }

  #[@test]
  public function parents_method_exists() {
    $this->assertTrue($this->define()->hasMethod('equals'));
  }

  #[@test]
  public function parents_field_exists() {
    $this->assertTrue($this->define('', 'lang.Throwable')->hasField('message'));
  }

  #[@test]
  public function static_initializer_is_invoked() {
    $class= $this->define('', 'lang.Object', [], '{
      public static $initializerCalled= false;
      static function __static() { self::$initializerCalled= true; }
    }');
    $this->assertTrue($class->getField('initializerCalled')->get(null));
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_define_class_with_non_existant_parent() {
    $this->define('', '@@nonexistant@@');
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_define_class_with_null_parent() {
    $this->define('', null);
  }

  #[@test, @expect('lang.ClassNotFoundException'), @values([
  #  [['@@nonexistant@@']],
  #  [['lang.Runnable', '@@nonexistant@@']],
  #  [['@@nonexistant@@', 'lang.Runnable']]
  #])]
  public function cannot_define_class_with_non_existant_interface($list) {
    $this->define('', 'lang.Object', $list);
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_define_class_with_null_interface() {
    $this->define('', 'lang.Object', [null]);
  }

  #[@test]
  public function closure_map_style_declaring_field() {
    $class= $this->define('', 'lang.Object', [], ['fixture' => null]);
    $this->assertTrue($class->hasField('fixture'));
  }

  #[@test]
  public function closure_map_style_declaring_method() {
    $class= $this->define('', 'lang.Object', [], ['fixture' => function() { }]);
    $this->assertTrue($class->hasMethod('fixture'));
  }

  #[@test]
  public function closure_map_field_access() {
    $class= $this->define('', 'lang.Object', [], ['fixture' => 'Test']);
    $instance= $class->newInstance();
    $this->assertEquals('Test', $class->getField('fixture')->get($instance));
  }

  #[@test]
  public function closure_map_method_invocation() {
    $class= $this->define('', 'lang.Object', [], ['fixture' => function($a, $b) { return [$this, $a, $b]; }]);
    $instance= $class->newInstance();
    $this->assertEquals([$instance, 1, 2], $class->getMethod('fixture')->invoke($instance, [1, 2]));
  }
}
