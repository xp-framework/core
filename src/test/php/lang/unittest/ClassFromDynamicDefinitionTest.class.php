<?php namespace lang\unittest;

use lang\{DynamicClassLoader, IClassLoader};
use test\{Assert, Ignore, Test};

class ClassFromDynamicDefinitionTest extends ClassFromUriTest {

  /** Creates fixture */
  protected function newFixture(): IClassLoader {
    return DynamicClassLoader::instanceFor('test');
  }

  /**
   * Creates underlying base for class loader, e.g. a directory or a .XAR file
   *
   * @return  lang.unittest.ClassFromUriBase
   */
  protected static function baseImpl() {
    return new class() extends ClassFromUriBase {
      protected $t= NULL;

      public function create() {
        // NOOP
      }

      public function delete() {
        // NOOP
      }

      public function newType($type, $name) {
        if (FALSE === ($p= strrpos($name, "."))) {
          $class= $name;
          $ns= "";
        } else {
          $class= substr($name, $p + 1);
          $ns= "namespace ".strtr(substr($name, 0, $p), ".", "\\").";";
        }

        \lang\DynamicClassLoader::instanceFor("test")->setClassBytes($name, sprintf(
          "%s %s %s { }",
          $ns,
          $type,
          $class
        ));
      }

      public function newFile($name, $contents) {
        // Not supported
      }

      public function path() {
        return "dyn://";
      }
    };
  }

  #[Test]
  public function provides_a_relative_path_in_root() {
    Assert::true($this->fixture->providesUri('dyn://CLT1'));
  }

  #[Test]
  public function load_from_a_relative_path_in_root() {
    Assert::equals(
      $this->fixture->loadClass('CLT1'),
      $this->fixture->loadUri('dyn://CLT1')
    );
  }

  #[Test]
  public function from_a_relative_path() {
    Assert::equals(
      $this->fixture->loadClass('lang.unittest.CLT2'),
      $this->fixture->loadUri('dyn://lang.unittest.CLT2')
    );
  }

  #[Test, Ignore('Not applicablle for DynamicClassLoader\'s URIs')]
  public function from_a_relative_path_with_dot() {
  }

  #[Test, Ignore('Not applicablle for DynamicClassLoader\'s URIs')]
  public function from_a_relative_path_with_dot_dot() {
  }

  #[Test, Ignore('Not applicablle for DynamicClassLoader\'s URIs')]
  public function from_a_relative_path_with_multiple_directory_separators() {
  }

  #[Test, Ignore('Not applicablle for DynamicClassLoader\'s URIs')]
  public function from_an_absolute_path_in_root() {
  }

  #[Test, Ignore('Not applicablle for DynamicClassLoader\'s URIs')]
  public function from_an_absolute_path() {
  }
}