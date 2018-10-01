<?php namespace net\xp_framework\unittest\io;

use io\{Folder, Path, File, FolderEntries};
use lang\{IllegalArgumentException, Environment};

class FolderEntriesTest extends \unittest\TestCase {
  private $folder;

  /**
   * Sets up test case - initializes temp directory in %TEMP%
   *
   * @return void
   */
  public function setUp() {
    $this->folder= new Folder(Environment::tempDir(), md5(uniqid()).'.xp');
    $this->folder->exists() && $this->folder->unlink();
    $this->folder->create();
  }

  /**
   * Deletes directory in %TEMP% (including any files inside) if existant
   *
   * @return void
   */
  public function tearDown() {
    $this->folder->exists() && $this->folder->unlink();
  }

  #[@test]
  public function can_create_with_folder() {
    new FolderEntries($this->folder);
  }

  #[@test]
  public function can_create_with_uri() {
    new FolderEntries($this->folder->getURI());
  }

  #[@test]
  public function can_create_with_reference_to_current_directory() {
    new FolderEntries('.');
  }

  #[@test]
  public function can_create_with_reference_to_parent_directory() {
    new FolderEntries('..');
  }

  #[@test]
  public function can_create_with_path() {
    new FolderEntries(new Path($this->folder));
  }

  #[@test, @expect(IllegalArgumentException::class), @values([null, ''])]
  public function cannot_create_from_empty_name($value) {
    new FolderEntries($value);
  }

  #[@test]
  public function entries_iteration_for_empty_folder() {
    $this->assertEquals([], iterator_to_array(new FolderEntries($this->folder)));
  }

  #[@test]
  public function entries_iteration_with_one_file() {
    (new File($this->folder, 'one'))->touch();

    $this->assertEquals(
      ['one' => new Path($this->folder, 'one')],
      iterator_to_array(new FolderEntries($this->folder))
    );
  }

  #[@test]
  public function entries_iteration_with_files_and_directories() {
    (new File($this->folder, 'one'))->touch();
    (new Folder($this->folder, 'two'))->create();

    $this->assertEquals(
      ['one' => new Path($this->folder, 'one'), 'two' => new Path($this->folder, 'two')],
      iterator_to_array(new FolderEntries($this->folder))
    );
  }

  #[@test]
  public function entries_reiteration() {
    (new File($this->folder, 'one'))->touch();
    (new File($this->folder, 'two'))->touch();

    $expected= ['one' => new Path($this->folder, 'one'), 'two' => new Path($this->folder, 'two')];
    $entries= new FolderEntries($this->folder);
    $this->assertEquals(
      [$expected, $expected],
      [iterator_to_array($entries), iterator_to_array($entries)]
    );
  }

  #[@test]
  public function named() {
    $this->assertEquals(new Path($this->folder, 'test'), (new FolderEntries($this->folder))->named('test'));
  }

  #[@test]
  public function named_dot() {
    $this->assertEquals(new Path($this->folder), (new FolderEntries($this->folder))->named('.'));
  }
}