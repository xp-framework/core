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
   * @param   string $annotations
   * @param   var[] $parents
   * @param   string $bytes
   * @return  lang.XPClass
   */
  protected function define($annotations= '', $parents= [], $bytes= '{}') {
    return $this->defineType($annotations, function($spec) use($parents, $bytes) {
      return ClassLoader::defineInterface($spec, $parents, $bytes);
    });
  }

  #[@test]
  public function given_parent_is_inherited() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable')],
      $this->define('', ['lang.Runnable'])->getInterfaces()
    );
  }

  #[@test]
  public function given_parent_class_is_inherited() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable')],
      $this->define('', [XPClass::forName('lang.Runnable')])->getInterfaces()
    );
  }

  #[@test]
  public function given_parents_are_inherited() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable'), XPClass::forName('lang.Closeable')],
      $this->define('', ['lang.Runnable', 'lang.Closeable'])->getInterfaces()
    );
  }

  #[@test]
  public function given_parent_classes_are_inherited() {
    $this->assertEquals(
      [XPClass::forName('lang.Runnable'), XPClass::forName('lang.Closeable')],
      $this->define('', [XPClass::forName('lang.Runnable'), XPClass::forName('lang.Closeable')])->getInterfaces()
    );
  }

  #[@test]
  public function parents_method_exists() {
    $this->assertTrue($this->define('', ['lang.Runnable'])->hasMethod('run'));
  }

  #[@test]
  public function method_exists() {
    $class= $this->define('', ['lang.Runnable'], '{ public function runAs($user); }');
    $this->assertTrue($class->hasMethod('runAs'));
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_define_interface_with_non_existant_parent() {
    $this->define('', ['@@nonexistant@@']);
  }

  #[@test, @expect('lang.ClassNotFoundException')]
  public function cannot_define_interface_with_null_parent() {
    $this->define('', [null]);
  }

  #[@test]
  public function closure_map_style_declaring_method() {
    $class= $this->define('', ['lang.Runnable'], ['fixture' => function() { }]);
    $this->assertTrue($class->hasMethod('fixture'));
  }
}