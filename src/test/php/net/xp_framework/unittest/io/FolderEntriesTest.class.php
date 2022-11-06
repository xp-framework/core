<?php namespace net\xp_framework\unittest\io;

use io\{File, Folder, FolderEntries, Path};
use lang\{Environment, IllegalArgumentException};
use unittest\{Expect, Test, Values};

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

  #[Test]
  public function can_create_with_folder() {
    new FolderEntries($this->folder);
  }

  #[Test]
  public function can_create_with_uri() {
    new FolderEntries($this->folder->getURI());
  }

  #[Test]
  public function can_create_with_reference_to_current_directory() {
    new FolderEntries('.');
  }

  #[Test]
  public function can_create_with_reference_to_parent_directory() {
    new FolderEntries('..');
  }

  #[Test]
  public function can_create_with_path() {
    new FolderEntries(new Path($this->folder));
  }

  #[Test, Expect(IllegalArgumentException::class), Values([null, ''])]
  public function cannot_create_from_empty_name($value) {
    new FolderEntries($value);
  }

  #[Test]
  public function entries_iteration_for_empty_folder() {
    $this->assertEquals([], iterator_to_array(new FolderEntries($this->folder)));
  }

  #[Test]
  public function entries_iteration_with_one_file() {
    (new File($this->folder, 'one'))->touch();

    $this->assertEquals(
      ['one' => new Path($this->folder, 'one')],
      iterator_to_array(new FolderEntries($this->folder))
    );
  }

  #[Test]
  public function entries_iteration_with_files_and_directories() {
    (new File($this->folder, 'one'))->touch();
    (new Folder($this->folder, 'two'))->create();

    $this->assertEquals(
      ['one' => new Path($this->folder, 'one'), 'two' => new Path($this->folder, 'two')],
      iterator_to_array(new FolderEntries($this->folder))
    );
  }

  #[Test]
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

  #[Test]
  public function named() {
    $this->assertEquals(new Path($this->folder, 'test'), (new FolderEntries($this->folder))->named('test'));
  }

  #[Test]
  public function named_dot() {
    $this->assertEquals(new Path($this->folder), (new FolderEntries($this->folder))->named('.'));
  }

  #[Test]
  public function entries_matching() {
    (new File($this->folder, 'a.txt'))->touch();
    (new File($this->folder, 'b.txt'))->touch();
    (new File($this->folder, 'not-found'))->touch();

    $this->assertEquals(
      ['a.txt' => new Path($this->folder, 'a.txt'), 'b.txt' => new Path($this->folder, 'b.txt')],
      iterator_to_array((new FolderEntries($this->folder))->matching('*.txt'))
    );
  }

  #[Test]
  public function entries_matching_ignores_hidden_files() {
    (new File($this->folder, '.hidden.txt'))->touch();

    $this->assertEquals([], iterator_to_array((new FolderEntries($this->folder))->matching('*.txt')));
  }

  #[Test]
  public function entries_matching_is_case_sensitive() {
    (new File($this->folder, 'C.TXT'))->touch();

    $this->assertEquals([], iterator_to_array((new FolderEntries($this->folder))->matching('*.txt')));
  }

  #[Test]
  public function entries_matching_supports_braces() {
    (new File($this->folder, 'a.txt'))->touch();
    (new File($this->folder, 'C.TXT'))->touch();

    $this->assertEquals(
      ['a.txt' => new Path($this->folder, 'a.txt'), 'C.TXT' => new Path($this->folder, 'C.TXT')],
      iterator_to_array((new FolderEntries($this->folder))->matching('*.{txt,TXT}'))
    );
  }
}