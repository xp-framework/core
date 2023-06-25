<?php namespace util\unittest;

use io\streams\MemoryOutputStream;
use test\{Assert, Test};
use util\Properties;

class PropertyWritingTest {
  
  /** @return util.Properties */
  private function newFixture() {
    $fixture= new Properties(null);
    $fixture->create();
    return $fixture;
  }
  
  /**
   * Verifies the saved property file equals a given expected source string
   *
   * @param   util.Properties $fixture
   * @param   string $expected
   * @throws  unittest.AssertionFailedError
   */
  protected function assertSavedFixtureEquals($fixture, $expected) {
    $out= new MemoryOutputStream();
    $fixture->store($out);
    Assert::equals(preg_replace('/^ +/m', '', trim($expected)), trim($out->bytes())); 
  }

  #[Test]
  public function string() {
    $fixture= $this->newFixture();
    $fixture->writeString('section', 'key', 'value');
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key="value"
    ');
  }

  #[Test]
  public function emptyString() {
    $fixture= $this->newFixture();
    $fixture->writeString('section', 'key', '');
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key=""
    ');
  }

  #[Test]
  public function integer() {
    $fixture= $this->newFixture();
    $fixture->writeInteger('section', 'key', 1);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key=1
    ');
  }

  #[Test]
  public function float() {
    $fixture= $this->newFixture();
    $fixture->writeFloat('section', 'key', 1.5);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key=1.5
    ');
  }

  #[Test]
  public function boolTrue() {
    $fixture= $this->newFixture();
    $fixture->writeFloat('section', 'key', true);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key=1
    ');
  }

  #[Test]
  public function boolFalse() {
    $fixture= $this->newFixture();
    $fixture->writeFloat('section', 'key', false);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key=0
    ');
  }

  #[Test]
  public function intArray() {
    $fixture= $this->newFixture();
    $fixture->writeArray('section', 'key', [1, 2, 3]);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key[]=1
      key[]=2
      key[]=3
    ');
  }

  #[Test]
  public function emptyArray() {
    $fixture= $this->newFixture();
    $fixture->writeArray('section', 'key', []);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key=
    ');
  }

  #[Test]
  public function mapOneElement() {
    $fixture= $this->newFixture();
    $fixture->writeMap('section', 'key', ['color' => 'green']);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key[color]="green"
    ');
  }

  #[Test]
  public function mapTwoElements() {
    $fixture= $this->newFixture();
    $fixture->writeMap('section', 'key', ['color' => 'green', 'size' => 'L']);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key[color]="green"
      key[size]="L"
    ');
  }

  #[Test]
  public function emptyMap() {
    $fixture= $this->newFixture();
    $fixture->writeMap('section', 'key', []);
    $this->assertSavedFixtureEquals($fixture, '
      [section]
      key=
    ');
  }

  #[Test]
  public function comment() {
    $fixture= $this->newFixture();
    $fixture->writeComment('section', 'Hello');
    $this->assertSavedFixtureEquals($fixture, '
      [section]

      ; Hello
    ');
  }

  #[Test]
  public function comments() {
    $fixture= $this->newFixture();
    $fixture->writeComment('section', 'Hello');
    $fixture->writeComment('section', 'World');
    $this->assertSavedFixtureEquals($fixture, '
      [section]

      ; Hello

      ; World
    ');
  }
}