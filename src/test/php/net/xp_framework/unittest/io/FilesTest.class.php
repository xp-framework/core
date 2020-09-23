<?php namespace net\xp_framework\unittest\io;

use io\streams\{MemoryInputStream, MemoryOutputStream, Streams};
use io\{File, Files};
use unittest\{Test, TestCase};

class FilesTest extends TestCase {

  #[Test]
  public function read() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));
    $this->assertEquals('Test', Files::read($f));
  }

  #[Test]
  public function read_from_uri() {
    $in= new MemoryInputStream('Test');
    $this->assertEquals('Test', Files::read(Streams::readableUri($in)));
  }

  #[Test]
  public function write_returns_number_of_written_bytes() {
    $f= new File(Streams::writeableFd(new MemoryOutputStream()));
    $this->assertEquals(4, Files::write($f, 'Test'));
  }

  #[Test]
  public function write_bytes() {
    $out= new MemoryOutputStream();
    Files::write(new File(Streams::writeableFd($out)), 'Test');
    $this->assertEquals('Test', $out->bytes());
  }

  #[Test]
  public function write_bytes_to_uri() {
    $out= new MemoryOutputStream();
    Files::write(Streams::writeableUri($out), 'Test');
    $this->assertEquals('Test', $out->bytes());
  }

  #[Test]
  public function overwrite_bytes() {
    $out= new MemoryOutputStream('Existing');
    Files::write(new File(Streams::writeableFd($out)), 'Test');
    $this->assertEquals('Test', $out->bytes());
  }

  #[Test]
  public function append_bytes() {
    $out= new MemoryOutputStream();
    Files::append(new File(Streams::writeableFd($out)), 'Test');
    $this->assertEquals('Test', $out->bytes());
  }

  #[Test]
  public function append_bytes_to_uri() {
    $out= new MemoryOutputStream();
    Files::append(Streams::writeableUri($out), 'Test');
    $this->assertEquals('Test', $out->bytes());
  }

  #[Test]
  public function append_bytes_to_existing() {
    $out= new MemoryOutputStream('Existing');
    Files::append(new File(Streams::writeableFd($out)), 'Test');
    $this->assertEquals('ExistingTest', $out->bytes());
  }

  #[Test]
  public function append_bytes_to_existing_uri() {
    $out= new MemoryOutputStream('Existing');
    Files::append(Streams::writeableUri($out), 'Test');
    $this->assertEquals('ExistingTest', $out->bytes());
  }

  #[Test]
  public function read_returns_less_than_size() {
    $f= new File(Streams::readableFd(new class('Test') extends MemoryInputStream {
      public function read($size= 4096) { return parent::read(min(1, $size)); }
    }));
    $this->assertEquals('Test', Files::read($f));
  }

  #[Test]
  public function methods_can_be_used_on_instance() {
    $f= new File(Streams::readableFd(new MemoryInputStream('Test')));

    $files= new Files();
    $this->assertEquals('Test', $files->read($f));
  }
}