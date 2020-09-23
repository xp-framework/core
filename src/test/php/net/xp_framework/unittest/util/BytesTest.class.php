<?php namespace net\xp_framework\unittest\util;

use lang\{FormatException, IllegalArgumentException, IndexOutOfBoundsException};
use unittest\{Expect, Test, Values};
use util\Bytes;

/**
 * TestCase for Bytes class
 *
 * @see   xp://util.Bytes
 */
class BytesTest extends \unittest\TestCase {

  #[Test]
  public function creating_an_empty_bytes_without_supplying_parameters() {
    $this->assertEquals(0, (new Bytes())->size());
  }

  #[Test]
  public function creating_an_empty_bytes_from_an_empty_string() {
    $this->assertEquals(0, (new Bytes(''))->size());
  }

  #[Test]
  public function creating_an_empty_bytes_from_an_empty_array() {
    $this->assertEquals(0, (new Bytes([]))->size());
  }

  #[Test]
  public function fromString() {
    $b= new Bytes('abcd');
    $this->assertEquals(4, $b->size());
    $this->assertEquals(97, $b[0]);
    $this->assertEquals(98, $b[1]);
    $this->assertEquals(99, $b[2]);
    $this->assertEquals(100, $b[3]);
  }

  #[Test]
  public function fromIntegerArray() {
    $b= new Bytes([97, 98, 99, 100]);
    $this->assertEquals(4, $b->size());
    $this->assertEquals(97, $b[0]);
    $this->assertEquals(98, $b[1]);
    $this->assertEquals(99, $b[2]);
    $this->assertEquals(100, $b[3]);
  }
 
  #[Test]
  public function fromCharArray() {
    $b= new Bytes(['a', 'b', 'c', 'd']);
    $this->assertEquals(4, $b->size());
    $this->assertEquals(97, $b[0]);
    $this->assertEquals(98, $b[1]);
    $this->assertEquals(99, $b[2]);
    $this->assertEquals(100, $b[3]);
  }

  #[Test]
  public function fromByteArray() {
    $b= new Bytes([97, 98, 99, 100]);
    $this->assertEquals(4, $b->size());
    $this->assertEquals(97, $b[0]);
    $this->assertEquals(98, $b[1]);
    $this->assertEquals(99, $b[2]);
    $this->assertEquals(100, $b[3]);
  }
 
  #[Test, Expect(IllegalArgumentException::class)]
  public function illegalConstructorArgument() {
    new Bytes(1);
  }

  #[Test]
  public function sizeChangesAfterAppending() {
    $b= new Bytes();
    $this->assertEquals(0, $b->size());
    $b[]= 1;
    $this->assertEquals(1, $b->size());
  }

  #[Test]
  public function sizeChangesAfterRemoving() {
    $b= new Bytes("\0");
    $this->assertEquals(1, $b->size());
    unset($b[0]);
    $this->assertEquals(0, $b->size());
  }

  #[Test]
  public function sizeDoesNotChangeWhenSetting() {
    $b= new Bytes("\0");
    $this->assertEquals(1, $b->size());
    $b[0]= "\1";
    $this->assertEquals(1, $b->size());
  }

  #[Test]
  public function appendInteger() {
    $b= new Bytes();
    $b[]= 1;
    $this->assertEquals(1, $b[0]);
  }

  #[Test]
  public function appendChar() {
    $b= new Bytes();
    $b[]= "\1";
    $this->assertEquals(1, $b[0]);
  }

  #[Test]
  public function appendByte() {
    $b= new Bytes();
    $b[]= 1;
    $this->assertEquals(1, $b[0]);
  }

  #[Test]
  public function setInteger() {
    $b= new Bytes("\1\2");
    $b[0]= 3;
    $this->assertEquals(3, $b[0]);
  }

  #[Test]
  public function setChar() {
    $b= new Bytes("\1\2");
    $b[0]= "\3";
    $this->assertEquals(3, $b[0]);
  }

  #[Test]
  public function setByte() {
    $b= new Bytes("\1\2");
    $b[0]= 3;
    $this->assertEquals(3, $b[0]);
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
    $this->assertFalse(isset($b[-1]), 'offset -1');
    $this->assertTrue(isset($b[0]), 'offset 0');
    $this->assertTrue(isset($b[5]), 'offset 5');
    $this->assertFalse(isset($b[6]), 'offset 6');
  }

  #[Test]
  public function removingFromBeginning() {
    $b= new Bytes('GIF89a');
    unset($b[0]);
    $this->assertEquals(new Bytes('IF89a'), $b);
  }

  #[Test]
  public function removingFromEnd() {
    $b= new Bytes('GIF89a');
    unset($b[5]);
    $this->assertEquals(new Bytes('GIF89'), $b);
  }

  #[Test]
  public function removingInBetween() {
    $b= new Bytes('GIF89a');
    unset($b[3]);
    $this->assertEquals(new Bytes('GIF9a'), $b);
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
    $this->assertEquals(0, $b[0]);
    $this->assertEquals(65, $b[1]);
    $this->assertEquals(66, $b[2]);
  }

  #[Test]
  public function binarySafeInBetween() {
    $b= new Bytes(['A', "\0", 'B']);
    $this->assertEquals(65, $b[0]);
    $this->assertEquals(0, $b[1]);
    $this->assertEquals(66, $b[2]);
  }

  #[Test]
  public function binarySafeInEnd() {
    $b= new Bytes(['A', 'B', "\0"]);
    $this->assertEquals(65, $b[0]);
    $this->assertEquals(66, $b[1]);
    $this->assertEquals(0, $b[2]);
  }

  #[Test]
  public function abcBytesToString() {
    $this->assertEquals(
      'util.Bytes(6)@{@ ABC!}', 
      (new Bytes('@ ABC!'))->toString()
    );
  }

  #[Test]
  public function controlCharsToString() {
    $this->assertEquals(
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
    $this->assertEquals(
      'util.Bytes(9)@{A\303\244O\303\266U\303\274}', 
      (new Bytes('AäOöUü'))->toString()
    );
  }

  #[Test]
  public function stringCasting() {
    $this->assertEquals('Hello', (string)new Bytes('Hello'));
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
    $this->assertEquals(1000, $r['number']);
  }

  /**
   * Test creating bytes from an integer using "N" as format
   * (unsigned long (always 32 bit, big endian byte order))
   *
   * @see     php://pack
   */
  #[Test]
  public function packUnsignedLong() {
    $this->assertEquals(new Bytes("\000\000\003\350"), new Bytes(pack('N', 1000)));
  }

  #[Test]
  public function worksWithEchoStatement() {
    ob_start();
    echo new Bytes('ü');
    $this->assertEquals('ü', ob_get_clean());
  }

  #[Test]
  public function integerArrayToBytes() {
    $b= new Bytes([228, 246, 252]);
    $this->assertEquals(-28, $b[0]);
    $this->assertEquals(-10, $b[1]);
    $this->assertEquals(-4, $b[2]);
  }

  #[Test]
  public function byteArrayToBytes() {
    $b= new Bytes([-28]);
    $this->assertEquals(-28, $b[0]);
  }

  #[Test]
  public function iteration() {
    $c= ['H', "\303", "\244", 'l', 'l', 'o'];
    $b= new Bytes($c);
    foreach ($b as $i => $byte) {
      $this->assertEquals($c[$i], chr($byte));
    }
    $this->assertEquals($i, sizeof($c)- 1);
  }

  #[Test, Values([[new Bytes('Test'), 0], [new Bytes('T'), +3], [new Bytes('Testing'), -3], [null, 1]])]
  public function compare($value, $expected) {
    $this->assertEquals($expected, (new Bytes('Test'))->compareTo($value));
  }
}