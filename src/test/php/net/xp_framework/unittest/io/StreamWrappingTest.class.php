<?php namespace net\xp_framework\unittest\io;

use io\IOException;
use io\streams\{InputStream, MemoryInputStream, MemoryOutputStream, Streams};
use unittest\{Assert, Expect, Test, Values};

class StreamWrappingTest {

  #[Test]
  public function read_using_fread() {
    $buffer= 'Hello World';
    $m= new MemoryInputStream($buffer);

    $fd= Streams::readableFd($m);
    $read= fread($fd, strlen($buffer));
    fclose($fd);

    Assert::equals($buffer, $read);
  }

  #[Test]
  public function read_using_fgets() {
    $buffer= 'Hello World';
    $m= new MemoryInputStream($buffer);

    $fd= Streams::readableFd($m);
    $read= fgets($fd, strlen($buffer) + 1);
    fclose($fd);

    Assert::equals($buffer, $read);
  }

  #[Test]
  public function read_using_fgets_including_newline() {
    $buffer= "Hello World\n";
    $m= new MemoryInputStream($buffer);

    $fd= Streams::readableFd($m);
    $read= fgets($fd, strlen($buffer) + 1);
    fclose($fd);
    
    Assert::equals($buffer, $read);
  }

  #[Test]
  public function endOfFile() {
    $fd= Streams::readableFd(new MemoryInputStream(str_repeat('x', 10)));
    Assert::false(feof($fd), 'May not be at EOF directly after opening');

    fread($fd, 5);
    Assert::false(feof($fd), 'May not be at EOF after reading only half of the bytes');

    fread($fd, 5);
    Assert::true(feof($fd), 'Must be at EOF after having read all of the bytes');

    fclose($fd);
  }

  #[Test]
  public function fstat() {
    $fd= Streams::readableFd(new MemoryInputStream(str_repeat('x', 10)));
    $stat= fstat($fd);
    Assert::equals(10, $stat['size']);

    fread($fd, 5);
    $stat= fstat($fd);
    Assert::equals(10, $stat['size']);
    
    fclose($fd);
  }

  #[Test]
  public function statExistingReadableUri() {
    $uri= Streams::readableUri(new MemoryInputStream(str_repeat('x', 10)));
    $stat= stat($uri);
    Assert::equals(0, $stat['size']);
  }

  #[Test]
  public function statExistingWriteableUri() {
    $uri= Streams::writeableUri(new MemoryOutputStream());
    $stat= stat($uri);
    Assert::equals(0, $stat['size']);
  }

  #[Test]
  public function statNonExistingReadableUri() {
    $uri= Streams::readableUri(new MemoryInputStream(str_repeat('x', 10)));
    fclose(fopen($uri, 'r'));
    Assert::false(stat($uri));
    \xp::gc(__FILE__);
  }

  #[Test]
  public function statNonExistingWriteableUri() {
    $uri= Streams::writeableUri(new MemoryOutputStream());
    fclose(fopen($uri, 'w'));
    Assert::false(stat($uri));
    \xp::gc(__FILE__);
  }

  #[Test]
  public function tellFromReadable() {
    $fd= Streams::readableFd(new MemoryInputStream(str_repeat('x', 10)));
    Assert::equals(0, ftell($fd));

    fread($fd, 5);
    Assert::equals(5, ftell($fd));

    fread($fd, 5);
    Assert::equals(10, ftell($fd));
    
    fclose($fd);
  }

  #[Test]
  public function tellFromWriteable() {
    $fd= Streams::writeableFd(new MemoryOutputStream());
    Assert::equals(0, ftell($fd));

    fwrite($fd, str_repeat('x', 5));
    Assert::equals(5, ftell($fd));

    fwrite($fd, str_repeat('x', 5));
    Assert::equals(10, ftell($fd));
    
    fclose($fd);
  }

  #[Test]
  public function writing() {
    $buffer= 'Hello World';
    $m= new MemoryOutputStream();

    $fd= Streams::writeableFd($m);
    $written= fwrite($fd, $buffer);
    fclose($fd);
    
    Assert::equals(strlen($buffer), $written);
    Assert::equals($buffer, $m->bytes());
  }

  #[Test, Expect(IOException::class)]
  public function reading_from_writeable_fd_raises_exception() {
    $fd= Streams::writeableFd(new MemoryOutputStream());
    fread($fd, 1024);
  }

  #[Test, Expect(IOException::class)]
  public function writing_to_readable_fd_raises_exception() {
    $fd= Streams::readableFd(new MemoryInputStream(''));
    fwrite($fd, 1024);
  }

  #[Test, Values(['', 'Hello', "Hello\nWorld\n"])]
  public function readAll($value) {
    Assert::equals($value, Streams::readAll(new MemoryInputStream($value)));
  }

  #[Test, Expect(IOException::class)]
  public function readAll_propagates_exception() {
    Streams::readAll(new class() implements InputStream {
      public function read($limit= 8192) { throw new IOException('FAIL'); }
      public function available() { return 1; }
      public function close() { }
    });
  }

  #[Test]
  public function read_while_not_eof() {
    $fd= Streams::readableFd(new MemoryInputStream(str_repeat('x', 1024)));
    $l= [];
    while (!feof($fd)) {
      $c= fread($fd, 128);
      $l[]= strlen($c);
    }
    fclose($fd);
    Assert::equals([128, 128, 128, 128, 128, 128, 128, 128], $l);
  }

  #[Test, Values([0, 10, 10485760])]
  public function file_get_contents($length) {
    $data= str_repeat('x', $length);
    Assert::equals(
      $data,
      file_get_contents(Streams::readableUri(new MemoryInputStream($data)))
    );
  }

  #[Test]
  public function is_file() {
    Assert::true(is_file(Streams::readableUri(new MemoryInputStream('Hello'))));
  }
}