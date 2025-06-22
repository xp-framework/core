<?php namespace io\unittest;

use ArrayIterator;
use io\streams\{InputStream, MemoryInputStream, SequenceInputStream};
use lang\IllegalArgumentException;
use test\{Assert, Expect, Test};

class SequenceInputStreamTest {

  /** Drains a stream */
  private function drain(InputStream $stream): array {
    $r= [];
    while ($available= $stream->available()) {
      $r[]= [$available, $stream->read()];
    }
    $r[]= [$stream->available(), $stream->read()];
    return $r;
  }

  /** Creates a memory input stream with a `closed` property */
  private function closeable(string $input): MemoryInputStream {
    return new class($input) extends MemoryInputStream {
      public $closed= false;
      public function close() { $this->closed= true; }
    };
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function without_arguments() {
    new SequenceInputStream();
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function with_empty_array() {
    new SequenceInputStream([]);
  }

  #[Test]
  public function drain_one() {
    $fixture= new SequenceInputStream(new MemoryInputStream('Test'));
    Assert::equals([[4, 'Test'], [0, '']], $this->drain($fixture));
  }

  #[Test]
  public function drain_multiple() {
    $fixture= new SequenceInputStream(
      new MemoryInputStream('One'),
      new MemoryInputStream('Two')
    );
    Assert::equals([[3, 'One'], [3, 'Two'], [0, '']], $this->drain($fixture));
  }

  #[Test]
  public function drain_array() {
    $fixture= new SequenceInputStream([
      new MemoryInputStream('One'),
      new MemoryInputStream('Two')
    ]);
    Assert::equals([[3, 'One'], [3, 'Two'], [0, '']], $this->drain($fixture));
  }

  #[Test]
  public function drain_iterator() {
    $fixture= new SequenceInputStream(new ArrayIterator([
      yield new MemoryInputStream('One'),
      yield new MemoryInputStream('Two')
    ]));
    Assert::equals([[3, 'One'], [3, 'Two'], [0, '']], $this->drain($fixture));
  }

  #[Test]
  public function drain_generator() {
    $streams= function() {
      yield new MemoryInputStream('One');
      yield new MemoryInputStream('Two');
    };
    $fixture= new SequenceInputStream($streams());
    Assert::equals([[3, 'One'], [3, 'Two'], [0, '']], $this->drain($fixture));
  }

  #[Test]
  public function using_only_read() {
    $fixture= new SequenceInputStream(
      new MemoryInputStream('One'),
      new MemoryInputStream('Two')
    );

    Assert::equals('One', $fixture->read());
    Assert::equals('Two', $fixture->read());
    Assert::equals('', $fixture->read());
  }

  #[Test]
  public function close_closes_all_streams() {
    $one= $this->closeable('One');
    $two= $this->closeable('Two');
    $fixture= new SequenceInputStream($one, $two);
    $fixture->close();

    Assert::equals([true, true], [$one->closed, $two->closed]);
  }

  #[Test]
  public function streams_closed_when_drained() {
    $one= $this->closeable('One');
    $two= $this->closeable('Two');
    $fixture= new SequenceInputStream($one, $two);
    $this->drain($fixture);
    $fixture->close();

    Assert::equals([true, true], [$one->closed, $two->closed]);
  }
}