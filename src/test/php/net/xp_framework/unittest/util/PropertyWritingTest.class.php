<?php namespace net\xp_framework\unittest\util;

use io\streams\MemoryOutputStream;
use unittest\Test;
use util\Properties;

/**
 * Testcase for util.Properties class.
 *
 * @see      xp://util.Properties
 */
class PropertyWritingTest extends \unittest\TestCase {
  protected $fixture= null;
  
  /**
   * Creates a new, empty properties file as fixture
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= new Properties(null);
    $this->fixture->create();
  }
  
  /**
   * Verifies the saved property file equals a given expected source string
   *
   * @param   string expected
   * @throws  unittest.AssertionFailedError
   */
  protected function assertSavedFixtureEquals($expected) {
    $out= new MemoryOutputStream();
    $this->fixture->store($out);
    $this->assertEquals(preg_replace('/^ +/m', '', trim($expected)), trim($out->bytes())); 
  }

  #[Test]
  public function string() {
    $this->fixture->writeString('section', 'key', 'value');
    $this->assertSavedFixtureEquals('
      [section]
      key="value"
    ');
  }

  #[Test]
  public function emptyString() {
    $this->fixture->writeString('section', 'key', '');
    $this->assertSavedFixtureEquals('
      [section]
      key=""
    ');
  }

  #[Test]
  public function integer() {
    $this->fixture->writeInteger('section', 'key', 1);
    $this->assertSavedFixtureEquals('
      [section]
      key=1
    ');
  }

  #[Test]
  public function float() {
    $this->fixture->writeFloat('section', 'key', 1.5);
    $this->assertSavedFixtureEquals('
      [section]
      key=1.5
    ');
  }

  #[Test]
  public function boolTrue() {
    $this->fixture->writeFloat('section', 'key', true);
    $this->assertSavedFixtureEquals('
      [section]
      key=1
    ');
  }

  #[Test]
  public function boolFalse() {
    $this->fixture->writeFloat('section', 'key', false);
    $this->assertSavedFixtureEquals('
      [section]
      key=0
    ');
  }

  #[Test]
  public function intArray() {
    $this->fixture->writeArray('section', 'key', [1, 2, 3]);
    $this->assertSavedFixtureEquals('
      [section]
      key[]=1
      key[]=2
      key[]=3
    ');
  }

  #[Test]
  public function emptyArray() {
    $this->fixture->writeArray('section', 'key', []);
    $this->assertSavedFixtureEquals('
      [section]
      key=
    ');
  }

  #[Test]
  public function mapOneElement() {
    $this->fixture->writeMap('section', 'key', ['color' => 'green']);
    $this->assertSavedFixtureEquals('
      [section]
      key[color]="green"
    ');
  }

  #[Test]
  public function mapTwoElements() {
    $this->fixture->writeMap('section', 'key', ['color' => 'green', 'size' => 'L']);
    $this->assertSavedFixtureEquals('
      [section]
      key[color]="green"
      key[size]="L"
    ');
  }

  #[Test]
  public function emptyMap() {
    $this->fixture->writeMap('section', 'key', []);
    $this->assertSavedFixtureEquals('
      [section]
      key=
    ');
  }

  #[Test]
  public function comment() {
    $this->fixture->writeComment('section', 'Hello');
    $this->assertSavedFixtureEquals('
      [section]

      ; Hello
    ');
  }

  #[Test]
  public function comments() {
    $this->fixture->writeComment('section', 'Hello');
    $this->fixture->writeComment('section', 'World');
    $this->assertSavedFixtureEquals('
      [section]

      ; Hello

      ; World
    ');
  }
}