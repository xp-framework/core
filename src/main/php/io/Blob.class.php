<?php namespace io;

use IteratorAggregate, Traversable;
use io\streams\{InputStream, IterableInputStream, FilterInputStream, Streams};
use lang\{Value, IllegalArgumentException};
use util\{Bytes, Objects};

/** @test io.unittest.BlobTest */
class Blob implements IteratorAggregate, Value {
  private $parts;
  private $iterator= null;
  public $meta= [];

  /**
   * Creates a new blob from parts
   *
   * @param  iterable|string|util.Bytes|io.streams.InputStream $parts
   * @param  [:var] $meta
   * @throws lang.IllegalArgumentException
   */
  public function __construct($parts= [], array $meta= []) {
    if ($parts instanceof InputStream) {
      $this->iterator= function() {
        static $started= false;

        return (function() use(&$started) {
          $started ? Streams::seek($this->parts, 0) : $started= true;
          while ($this->parts->available()) {
            yield $this->parts->read();
          }
        })();
      };
    } else if ($parts instanceof Bytes || is_string($parts)) {
      $this->iterator= function() { yield (string)$this->parts; };
    } else if (is_iterable($parts)) {
      $this->iterator= function() {
        foreach ($this->parts as $part) {
          yield (string)$part;
        }
      };
    } else {
      throw new IllegalArgumentException(sprintf(
        'Expected iterable|string|util.Bytes|io.streams.InputStream, have %s',
        typeof($parts)
      ));
    }

    $this->parts= $parts;
    $this->meta= $meta;
  }

  /** @return iterable */
  public function getIterator(): Traversable { return ($this->iterator)(); }

  /** @return util.Bytes */
  public function bytes() { 
    return $this->parts instanceof Bytes
      ? $this->parts
      : new Bytes(...($this->iterator)())
    ;
  }

  /** @return io.streams.InputStream */
  public function stream() {
    return $this->parts instanceof InputStream
      ? $this->parts
      : new IterableInputStream(($this->iterator)())
    ;
  }

  /** Creates a new blob with the given encoding applied */
  public function encoded(string $encoding, ?callable $filter= null): self {
    $meta= $this->meta;
    $meta['encoding']??= [];
    $meta['encoding'][]= $encoding;
    return new self(new FilterInputStream($this->stream(), $filter ?? $encoding), $meta);
  }

  /** @return iterable */
  public function slices(int $size= 8192) {
    $it= ($this->iterator)();
    $it->rewind();
    while ($it->valid()) {
      $slice= $it->current();
      $length= strlen($slice);
      $offset= 0;

      while ($length < $size) {
        $it->next();
        $slice.= $it->current();
        if (!$it->valid()) break;
      }

      while ($length - $offset > $size) {
        yield substr($slice, $offset, $size);
        $offset+= $size;
      }

      yield $offset ? substr($slice, $offset) : $slice;
      $it->next();
    }
  }

  /** @return string */
  public function hashCode() { return 'B'.Objects::hashOf($this->parts); }

  /** @return string */
  public function toString() { return nameof($this).'('.Objects::stringOf($this->parts).')'; }

  /**
   * Comparison
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self
      ? Objects::compare($this->parts, $value->parts)
      : 1
    ;
  }

  /** @return string */
  public function __toString() {
    $bytes= '';
    foreach (($this->iterator)() as $chunk) {
      $bytes.= $chunk;
    }
    return $bytes;
  }
}