<?php namespace net\xp_framework\unittest\reflection;

use lang\{ClassLoader, ElementNotFoundException};
use unittest\{Assert, Expect, Test};

/**
 * TestCase for classloading
 *
 * @see    xp://lang.ClassLoader#registerPath
 */
class ClassPathTest {
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

  #[Test]
  public function before() {
    try {
      $loader= $this->track(ClassLoader::registerPath('.', true));
      $loaders= ClassLoader::getLoaders();
      Assert::equals($loader, $loaders[0]);
    } finally {
      $this->tearDown();
    }
  } 

  #[Test]
  public function after() {
    try {
      $loader= $this->track(ClassLoader::registerPath('.', false));
      $loaders= ClassLoader::getLoaders();
      Assert::equals($loader, $loaders[sizeof($loaders)- 1]);
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function after_is_default() {
    try {
      $loader= $this->track(ClassLoader::registerPath('.'));
      $loaders= ClassLoader::getLoaders();
      Assert::equals($loader, $loaders[sizeof($loaders)- 1]);
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function before_via_inspect() {
    try {
      $loader= $this->track(ClassLoader::registerPath('!.', null));
      $loaders= ClassLoader::getLoaders();
      Assert::equals($loader, $loaders[0]);
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function after_via_inspect() {
    try {
      $loader= $this->track(ClassLoader::registerPath('.', null));
      $loaders= ClassLoader::getLoaders();
      Assert::equals($loader, $loaders[sizeof($loaders)- 1]);
    } finally {
      $this->tearDown();
    }
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function non_existant() {
    ClassLoader::registerPath('@@non-existant@@');
  } 
}