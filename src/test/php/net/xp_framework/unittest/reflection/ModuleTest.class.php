<?php namespace net\xp_framework\unittest\reflection;

use lang\ClassLoader;

/**
 * TestCase for modules
 *
 * @see   xp://lang.ClassLoader
 */
class ModuleTest extends \unittest\TestCase {
  public static $verify;
  protected $registered= [];

  /**
   * Register a loader with the CL
   *
   * @param  lang.IClassLoader $l
   */
  protected function register($l) {
    $this->registered[]= ClassLoader::registerLoader($l);
  }

  /**
   * Tears down test, removing all loaders registered with the CL.
   */
  public function tearDown() {
    foreach ($this->registered as $l) {
      ClassLoader::removeLoader($l);
    }
  }

  #[@test]
  public function simple_module() {
    $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/simple { }']));
  }

  #[@test]
  public function leading_php_tag_is_stripped() {
    $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagstart { }']));
  }

  #[@test]
  public function leading_and_trailing_php_tags_are_stripped() {
    $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagboth { } ?>']));
  }

  #[@test]
  public function trailing_php_tag_is_stripped() {
    $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/tagend { } ?>']));
  }

  #[@test]
  public function modules_static_initializer_is_invoked() {
    self::$verify= 0;
    $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/initialized {
      static function __static() {
        \net\xp_framework\unittest\reflection\ModuleTest::$verify++;
      }
    }']));
    $this->assertEquals(1, self::$verify);
  }

  #[@test]
  public function module_in_namespace() {
    $this->register(new LoaderProviding(['module.xp' => '<?php namespace net\xp_framework\unittest\reflection;
    module xp-framework/namespaced { 

    }']));
  }

  #[@test, @expect(class= 'lang.ElementNotFoundException', withMessage= '/Missing or malformed module-info/')]
  public function empty_module_file() {
    $this->register(new LoaderProviding(['module.xp' => '']));
  }

  #[@test, @expect(class= 'lang.ElementNotFoundException', withMessage= '/Missing or malformed module-info/')]
  public function module_without_name() {
    $this->register(new LoaderProviding(['module.xp' => 'module { }']));
  }
}
