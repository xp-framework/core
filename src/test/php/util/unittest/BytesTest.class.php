<?php namespace util\unittest;

use lang\{FormatException, IllegalArgumentException, IndexOutOfBoundsException};
use test\{Assert, Expect, Test, Values};
use util\Bytes;

class BytesTest {

  /** @return iterable */
  private function slices() {
    yield ['0:4', 'This'];         // start:stop
    yield ['5:7', 'is'];           // start:stop - with non-zero start
    yield ['5:-5', 'is a'];        // start:stop - with negative offset
    yield ['-4:-2', 'test'];       // start:stop - with negative offsets
    yield [':4', 'This'];          // :stop
    yield [':-5', 'This is a'];    // :stop - with negative offset
    yield ['5:', 'is a test'];     // start:
    yield ['-4:', 'test'];         // start: - at negative offset
    yield [':', 'This is a test']; // : - copy
  }

  /** @return iterable */
  private function comparing() {
    yield [new Bytes('Test'), 0];
    yield [new Bytes('T'), 1];
    yield [new Bytes('Testing'), -1];
    yield [null, 1];
  }

  #[Test]
  public function creating_an_empty_bytes_without_supplying_parameters() {
    Assert::equals(0, (new Bytes())->size());
  }

  #[Test]
  public function creating_an_empty_bytes_from_an_empty_string() {
    Assert::equals(0, (new Bytes(''))->size());
  }

  #[Test]
  public function creating_an_empty_bytes_from_an_empty_array() {
    Assert::equals(0, (new Bytes([]))->size());
  }

  #[Test]
  public function from_string() {
    $b= new Bytes('abcd');
    Assert::equals(4, $b->size());
    Assert::equals(97, $b[0]);
    Assert::equals(98, $b[1]);
    Assert::equals(99, $b[2]);
    Assert::equals(100, $b[3]);
  }

  #[Test]
  public function from_integer_array() {
    $b= new Bytes([97, 98, 99, 100]);
    Assert::equals(4, $b->size());
    Assert::equals(97, $b[0]);
    Assert::equals(98, $b[1]);
    Assert::equals(99, $b[2]);
    Assert::equals(100, $b[3]);
  }

  #[Test]
  public function from_char_array() {
    $b= new Bytes(['a', 'b', 'c', 'd']);
    Assert::equals(4, $b->size());
    Assert::equals(97, $b[0]);
    Assert::equals(98, $b[1]);
    Assert::equals(99, $b[2]);
    Assert::equals(100, $b[3]);
  }

  #[Test]
  public function from_byte_array() {
    $b= new Bytes([97, 98, 99, 100]);
    Assert::equals(4, $b->size());
    Assert::equals(97, $b[0]);
    Assert::equals(98, $b[1]);
    Assert::equals(99, $b[2]);
    Assert::equals(100, $b[3]);
  }

  #[Test]
  public function from_self() {
    $b= new Bytes(new Bytes('abcd'));
    Assert::equals(4, $b->size());
    Assert::equals(97, $b[0]);
    Assert::equals(98, $b[1]);
    Assert::equals(99, $b[2]);
    Assert::equals(100, $b[3]);
  }

  #[Test]
  public function from_var_args() {
    $b= new Bytes('ab', [99, 100]);
    Assert::equals(4, $b->size());
    Assert::equals(97, $b[0]);
    Assert::equals(98, $b[1]);
    Assert::equals(99, $b[2]);
    Assert::equals(100, $b[3]);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function illegalConstructorArgument() {
    new Bytes(1);
  }

  #[Test]
  public function sizeChangesAfterAppending() {
    $b= new Bytes();
    Assert::equals(0, $b->size());
    $b[]= 1;
    Assert::equals(1, $b->size());
  }

  #[Test]
  public function sizeChangesAfterRemoving() {
    $b= new Bytes("\0");
    Assert::equals(1, $b->size());
    unset($b[0]);
    Assert::equals(0, $b->size());
  }

  #[Test]
  public function sizeDoesNotChangeWhenSetting() {
    $b= new Bytes("\0");
    Assert::equals(1, $b->size());
    $b[0]= "\1";
    Assert::equals(1, $b->size());
  }

  #[Test]
  public function appendInteger() {
    $b= new Bytes();
    $b[]= 1;
    Assert::equals(1, $b[0]);
  }

  #[Test]
  public function appendChar() {
    $b= new Bytes();
    $b[]= "\1";
    Assert::equals(1, $b[0]);
  }

  #[Test]
  public function appendByte() {
    $b= new Bytes();
    $b[]= 1;
    Assert::equals(1, $b[0]);
  }

  #[Test]
  public function setInteger() {
    $b= new Bytes("\1\2");
    $b[0]= 3;
    Assert::equals(3, $b[0]);
  }

  #[Test]
  public function setChar() {
    $b= new Bytes("\1\2");
    $b[0]= "\3";
    Assert::equals(3, $b[0]);
  }

  #[Test]
  public function setByte() {
    $b= new Bytes("\1\2");
    $b[0]= 3;
    Assert::equals(3, $b[0]);
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function setNegative() {
    $b= new Bytes('negative');
    $b[-1]= 3;
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function setPastEnd() {
    $b= new Bytes('ends');
    $b[5]= 3;
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function getNegative() {
    $b= new Bytes('negative');
    $read= $b[-1];
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function getPastEnd() {
    $b= new Bytes('ends');
    $read= $b[5];
  }

  #[Test]
  public function testingOffsets() {
    $b= new Bytes('GIF89a');
    Assert::false(isset($b[-1]), 'offset -1');
    Assert::true(isset($b[0]), 'offset 0');
    Assert::true(isset($b[5]), 'offset 5');
    Assert::false(isset($b[6]), 'offset 6');
  }

  #[Test]
  public function removingFromBeginning() {
    $b= new Bytes('GIF89a');
    unset($b[0]);
    Assert::equals(new Bytes('IF89a'), $b);
  }

  #[Test]
  public function removingFromEnd() {
    $b= new Bytes('GIF89a');
    unset($b[5]);
    Assert::equals(new Bytes('GIF89'), $b);
  }

  #[Test]
  public function removingInBetween() {
    $b= new Bytes('GIF89a');
    unset($b[3]);
    Assert::equals(new Bytes('GIF9a'), $b);
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function removingNegative() {
    $b= new Bytes('negative');
    unset($b[-1]);
  }

  #[Test, Expect(IndexOutOfBoundsException::class)]
  public function removingPastEnd() {
    $b= new Bytes('ends');
    unset($b[5]);
  }

  #[Test]
  public function binarySafeBeginning() {
    $b= new Bytes(["\0", 'A', 'B']);
    Assert::equals(0, $b[0]);
    Assert::equals(65, $b[1]);
    Assert::equals(66, $b[2]);
  }

  #[Test]
  public function binarySafeInBetween() {
    $b= new Bytes(['A', "\0", 'B']);
    Assert::equals(65, $b[0]);
    Assert::equals(0, $b[1]);
    Assert::equals(66, $b[2]);
  }

  #[Test]
  public function binarySafeInEnd() {
    $b= new Bytes(['A', 'B', "\0"]);
    Assert::equals(65, $b[0]);
    Assert::equals(66, $b[1]);
    Assert::equals(0, $b[2]);
  }

  #[Test]
  public function abcBytesToString() {
    Assert::equals(
      'util.Bytes(6)@{@ ABC!}', 
      (new Bytes('@ ABC!'))->toString()
    );
  }

  #[Test]
  public function controlCharsToString() {
    Assert::equals(
      'util.Bytes(32)@{'.
      '\000\001\002\003\004\005\006\a'.     //  0 -  7
      '\b\t\n\v\f\r\016\017'.               //  8 - 15
      '\020\021\022\023\024\025\026\027'.   // 16 - 23
      '\030\031\032\033\034\035\036\037'.   // 24 - 31
      '}',
      (new Bytes(range(0, 31)))->toString()
    );
  }

  #[Test]
  public function umlautsToString() {
    Assert::equals(
      'util.Bytes(9)@{A\303\244O\303\266U\303\274}', 
      (new Bytes('AäOöUü'))->toString()
    );
  }

  #[Test]
  public function stringCasting() {
    Assert::equals('Hello', (string)new Bytes('Hello'));
  }

  /**
   * Test creating an integer from bytes using "N" as format
   * (unsigned long (always 32 bit, big endian byte order))
   *
   * @see     php://unpack
   */
  #[Test]
  public function unpackUnsignedLong() {
    $r= unpack('Nnumber', new Bytes("\000\000\003\350"));
    Assert::equals(1000, $r['number']);
  }

  /**
   * Test creating bytes from an integer using "N" as format
   * (unsigned long (always 32 bit, big endian byte order))
   *
   * @see     php://pack
   */
  #[Test]
  public function packUnsignedLong() {
    Assert::equals(new Bytes("\000\000\003\350"), new Bytes(pack('N', 1000)));
  }

  #[Test]
  public function worksWithEchoStatement() {
    ob_start();
    echo new Bytes('ü');
    Assert::equals('ü', ob_get_clean());
  }

  #[Test]
  public function integerArrayToBytes() {
    $b= new Bytes([228, 246, 252]);
    Assert::equals(-28, $b[0]);
    Assert::equals(-10, $b[1]);
    Assert::equals(-4, $b[2]);
  }

  #[Test]
  public function byteArrayToBytes() {
    $b= new Bytes([-28]);
    Assert::equals(-28, $b[0]);
  }

  #[Test]
  public function iteration() {
    $c= ['H', "\303", "\244", 'l', 'l', 'o'];
    $b= new Bytes($c);
    foreach ($b as $i => $byte) {
      Assert::equals($c[$i], chr($byte));
    }
    Assert::equals($i, sizeof($c)- 1);
  }

  #[Test, Values(from: 'slices')]
  public function slice($slice, $expected) {
    $b= new Bytes('This is a test');
    Assert::equals(new Bytes($expected), $b($slice));
  }

  #[Test, Values(from: 'comparing')]
  public function compare($value, $expected) {
    Assert::equals($expected, (new Bytes('Test'))->compareTo($value));
  }
}