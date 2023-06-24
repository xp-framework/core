<?php namespace net\xp_framework\unittest\reflection;

use lang\{ClassNotFoundException, MethodNotImplementedException};
use unittest\{After, Assert, Before, Expect, Test};

abstract class ClassFromUriTest {
  protected static $base;
  protected $fixture;

  /**
   * Creates underlying base for class loader, e.g. a directory or a .XAR file
   *
   * @return  net.xp_framework.unittest.reflection.ClassFromUriBase
   */
  protected static function baseImpl() {
    throw new MethodNotImplementedException('Implement in subclass!', __FUNCTION__);
  }

  #[Before]
  public static function createBase() {
    self::$base= static::baseImpl();
    self::$base->initialize(function($self) {
      $self->newType('class', 'CLT1');
      $self->newType('class', 'net.xp_framework.unittest.reflection.CLT2');
      $self->newFile('CLT1.txt', 'This is not a class');
    });
  }

  #[After]
  public static function cleanUp() {
    self::$base->delete();
  }

  /**
   * Creates fixture.
   *
   * @return   lang.IClassLoader
   */
  protected abstract function newFixture();

  /**
   * Initializes fixture member with the results from `newFixture()`.
   */
  #[Before]
  public function setUp() {
    $this->fixture= $this->newFixture();
  }

  /**
   * Compose a path from a list of elements
   *
   * @param  var... args either strings or a ClassFromUriBase instance
   * @return string
   */
  protected function compose(... $args) {
    $base= self::$base;
    return implode(DIRECTORY_SEPARATOR, array_map(
      function($e) use($base) {
        return $base === $e ? $base->path() : rtrim((string)$e, DIRECTORY_SEPARATOR);
      },
      $args
    ));
  }

  #[Test]
  public function provides_a_relative_path_in_root() {
    Assert::true($this->fixture->providesUri('CLT1.class.php'));
  }

  #[Test]
  public function load_from_a_relative_path_in_root() {
    Assert::equals(
      $this->fixture->loadClass('CLT1'),
      $this->fixture->loadUri('CLT1.class.php')
    );
  }

  #[Test]
  public function from_a_relative_path() {
    Assert::equals(
      $this->fixture->loadClass('net.xp_framework.unittest.reflection.CLT2'),
      $this->fixture->loadUri($this->compose('net', 'xp_framework', 'unittest', 'reflection', 'CLT2.class.php'))
    );
  }

  #[Test]
  public function from_a_relative_path_with_dot() {
    Assert::equals(
      $this->fixture->loadClass('CLT1'),
      $this->fixture->loadUri($this->compose('.', 'CLT1.class.php'))
    );
  }

  #[Test]
  public function from_a_relative_path_with_dot_dot() {
    Assert::equals(
      $this->fixture->loadClass('CLT1'),
      $this->fixture->loadUri($this->compose('net', 'xp_framework', '..', '..', 'CLT1.class.php'))
    );
  }

  #[Test]
  public function from_a_relative_path_with_multiple_directory_separators() {
    Assert::equals(
      $this->fixture->loadClass('CLT1'),
      $this->fixture->loadUri($this->compose('.', null, 'CLT1.class.php'))
    );
  }

  #[Test]
  public function from_an_absolute_path_in_root() {
    Assert::equals(
      $this->fixture->loadClass('CLT1'),
      $this->fixture->loadUri($this->compose(self::$base, 'CLT1.class.php'))
    );
  }

  #[Test]
  public function from_an_absolute_path() {
    Assert::equals(
      $this->fixture->loadClass('net.xp_framework.unittest.reflection.CLT2'),
      $this->fixture->loadUri($this->compose(self::$base, 'net', 'xp_framework', 'unittest', 'reflection', 'CLT2.class.php'))
    );
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function from_an_absolute_path_not_inside_cl_base() {
    $this->fixture->loadUri($this->compose(null, 'CLT1.class.php'));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function from_non_class_file() {
    $this->fixture->loadUri('CLT1.txt');
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function from_directory() {
    $this->fixture->loadUri($this->compose(self::$base, 'net', 'xp_framework'));
  }

  #[Test, Expect(ClassNotFoundException::class)]
  public function from_non_existant_file() {
    $this->fixture->loadUri($this->compose(self::$base, 'NonExistant.File'));
  }
}