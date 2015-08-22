<?php namespace net\xp_framework\unittest\reflection;

use lang\ClassLoader;
use lang\ElementNotFoundException;

/**
 * TestCase for classloading
 *
 * @see    xp://lang.ClassLoader#registerPath
 */
class ClassPathTest extends \unittest\TestCase {
  protected $registered= [];

  /**
   * Track registration of a class loader
   *
   * @param   lang.IClassLoader l
   * @return  lang.IClassLoader the given loader
   */
  protected function track($l) {
    $this->registered[]= $l;
    return $l;
  }

  /**
   * Removes all registered paths
   *
   * @return void
   */
  public function tearDown() {
    foreach ($this->registered as $l) {
      ClassLoader::removeLoader($l);
    }
  }

  #[@test]
  public function before() {
    $loader= $this->track(ClassLoader::registerPath('.', true));
    $loaders= ClassLoader::getLoaders();
    $this->assertEquals($loader, $loaders[0]);
  } 

  #[@test]
  public function after() {
    $loader= $this->track(ClassLoader::registerPath('.', false));
    $loaders= ClassLoader::getLoaders();
    $this->assertEquals($loader, $loaders[sizeof($loaders)- 1]);
  }

  #[@test]
  public function after_is_default() {
    $loader= $this->track(ClassLoader::registerPath('.'));
    $loaders= ClassLoader::getLoaders();
    $this->assertEquals($loader, $loaders[sizeof($loaders)- 1]);
  }

  #[@test]
  public function before_via_inspect() {
    $loader= $this->track(ClassLoader::registerPath('!.', null));
    $loaders= ClassLoader::getLoaders();
    $this->assertEquals($loader, $loaders[0]);
  }

  #[@test]
  public function after_via_inspect() {
    $loader= $this->track(ClassLoader::registerPath('.', null));
    $loaders= ClassLoader::getLoaders();
    $this->assertEquals($loader, $loaders[sizeof($loaders)- 1]);
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function non_existant() {
    ClassLoader::registerPath('@@non-existant@@');
  } 
}
