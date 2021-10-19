<?php namespace net\xp_framework\unittest\reflection;

use io\{File, Folder};
use lang\{Environment, FileSystemClassLoader, IClassLoader};

/**
 * TestCase for classloading
 *
 * @see  xp://lang.FileSystemClassLoader#loadUri
 */
class ClassFromFileSystemTest extends ClassFromUriTest {

  /** Creates fixture */
  protected function newFixture(): IClassLoader {
    return new FileSystemClassLoader(realpath(self::$base->path()));
  }

  /**
   * Creates underlying base for class loader, e.g. a directory or a .XAR file
   *
   * @return  net.xp_framework.unittest.reflection.ClassFromUriBase
   */
  protected static function baseImpl() {
    return new class() extends ClassFromUriBase {
      protected $t= NULL;

      public function create() {
        $this->t= new Folder(Environment::tempDir(), 'fsclt');
        $this->t->create();
      }

      public function delete() {
        $this->t->unlink();
      }

      public function newFile($name, $contents) {
        $file= new File($this->t, $name);
        $path= new Folder($file->getPath());
        $path->exists() || $path->create();

        $file->out()->write($contents);
      }

      public function path() {
        return rtrim($this->t->getURI(), DIRECTORY_SEPARATOR);
      }
    };
  }
}