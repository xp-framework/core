<?php namespace net\xp_framework\unittest\reflection;

use io\TempFile;
use lang\IClassLoader;
use lang\archive\{Archive, ArchiveClassLoader};
use unittest\Assert;

/**
 * TestCase for classloading
 *
 * @see  xp://lang.archive.ArchiveClassLoader#loadUri
 */
class ClassFromArchiveTest extends ClassFromUriTest {

  /** Creates fixture */
  protected function newFixture(): IClassLoader {
    return new ArchiveClassLoader(self::$base->archive());
  }

  /**
   * Creates underlying base for class loader, e.g. a directory or a .XAR file
   *
   * @return  net.xp_framework.unittest.reflection.ClassFromUriBase
   */
  protected static function baseImpl() {
    return new class() extends ClassFromUriBase {
      protected $t= NULL;

      public function initialize($initializer) {
        parent::initialize($initializer);

        // Create archive; it will be flushed to disk at this point
        $this->t->create();
      }

      public function create() {
        $this->t= new Archive(new TempFile('arcl'));
        $this->t->open(Archive::CREATE);
      }

      public function delete() {
        $this->t->file->unlink();
      }

      public function newFile($name, $contents) {

        // Always use forward slashes inside archive
        $this->t->addBytes(strtr($name, DIRECTORY_SEPARATOR, '/'), $contents);
      }

      public function archive() {
        return $this->t;
      }

      public function path() {
        return 'xar://'.$this->t->getURI().'?';
      }
    };
  }
}