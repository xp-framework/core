<?php namespace net\xp_framework\unittest\core\types;

use lang\types\Number;
use lang\types\Long;
use lang\types\Byte;
use lang\types\Short;
use lang\types\Integer;
use lang\types\Float;
use lang\types\Double;
use lang\IllegalArgumentException;

/**
 * Tests the number wrapper typess
 *
 * @deprecated Wrapper types will move to their own library
 * @see   xp://lang.types.Number
 * @see   xp://lang.types.Byte
 * @see   xp://lang.types.Short
 * @see   xp://lang.types.Integer
 * @see   xp://lang.types.Double
 * @see   xp://lang.types.Float
 */
class NumberTest extends \unittest\TestCase {

  /**
   * Tests a given type
   *
   * @param   lang.types.Number number
   * @param   int int
   * @param   float float
   */
  protected function testType(Number $number, $int, $float) {
    $this->assertEquals($int, $number->intValue(), 'intValue');
    $this->assertEquals($float, $number->doubleValue(), 'doubleValue');
    $this->assertEquals($number, clone($number), 'clone');
  }

  #[@test]
  public function longType() {
    $this->testType(new Long(0), 0, 0.0);
  }

  #[@test]
  public function byteType() {
    $this->testType(new Byte(0), 0, 0.0);
  }

  #[@test]
  public function shortType() {
    $this->testType(new Short(0), 0, 0.0);
  }

  #[@test]
  public function integerType() {
    $this->testType(new Integer(0), 0, 0.0);
  }

  #[@test]
  public function doubleType() {
    $this->testType(new Double(0), 0, 0.0);
  }

  #[@test]
  public function floatType() {
    $this->testType(new Float(0), 0, 0.0);
  }

  #[@test]
  public function an_integer_is_not_a_long() {
    $this->assertNotEquals(new Integer(1), new Long(1));
  }

  #[@test]
  public function a_byte_is_not_a_short() {
    $this->assertNotEquals(new Byte(1), new Short(1));
  }

  #[@test]
  public function a_double_is_not_a_float() {
    $this->assertNotEquals(new Double(1.0), new Float(1.0));
  }

  #[@test]
  public function shortNumericStringIsANumber() {
    $long= new Long('1');
    $this->assertEquals(1, $long->intValue());
  }

  #[@test, @values(['12389192458912430951248958921958154', '-12389192458912430951248958921958154'])]
  public function longNumericStringIsANumber($string) {
    $this->assertEquals($string, (new Long($string))->hashCode());
  }

  #[@test]
  public function zeroIsANumber() {
    $this->assertEquals(0, (new Long(0))->intValue());
  }

  #[@test, @values([['-1', -1], ['+1', 1]])]
  public function prefixedNumbersAreNumbers($arg, $num) {
    $this->assertEquals($num, (new Long($arg))->intValue());
  }

  #[@test, @values([['1e4', 1e4], ['1E4', 1e4], ['1e-4', 1e-4], ['1E-4', 1e-4]])]
  public function numbers_with_exponent_notation($arg, $num) {
    $this->assertEquals($num, (new Double($arg))->doubleValue());
  }

  #[@test, @values([['-1e4', -1e4], ['-1E4', -1e4], ['-1e-4', -1e-4], ['-1E-4', -1e-4]])]
  public function negative_numbers_with_exponent_notation($arg, $num) {
    $this->assertEquals($num, (new Double($arg))->doubleValue());
  }

  #[@test, @values([['1.5', 1.5], ['+0.5', +0.5], ['-0.5', -0.5], ['0.0', 0.0]])]
  public function floatingNotationIsANumber($arg, $num) {
    $this->assertEquals($num, (new Double($arg))->doubleValue());
  }

  #[@test]
  public function numberWithLeadingSpaceIsANumber() {
    $this->assertEquals(123, (new Long(' 123'))->intValue());
  }

  #[@test]
  public function hexNumbersAreNumbers() {
    $long= new Long('0xAAAA');
    $this->assertEquals(43690, $long->intValue());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function stringIsNotANumber() {
    Long::valueOf('string');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function booleanIsNotANumber() {
    Long::valueOf(true);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function nullIsNotANumber() {
    Long::valueOf(null);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function writtenNumberIsNotANumber() {
    Long::valueOf('one');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function commaNotationIsNotANumber() {
    Long::valueOf('1,1');
  }

  #[@test, @values(['1E+', '1E-', '1E', 'E+4', 'E-4', 'E4', 'E']), @expect(IllegalArgumentException::class)]
  public function brokenExponentNotationIsNotANumber($value) {
    Long::valueOf($value);
  }

  #[@test, @values(['..5', '--1', '++1']), @expect(IllegalArgumentException::class)]
  public function doubleLeadingSignsAreNotNumeric($value) {
    Long::valueOf($value);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function leadingLetterIsNotANumber() {
    Long::valueOf('a123');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function currencyValueIsNotANumber() {
    Long::valueOf('$44.00');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function whitespaceSeparatedNumbersAreNotNumeric() {
    Long::valueOf('4 4');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointNumberIsNotLong() {
    Long::valueOf(4.4);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointInStringIsNotLong() {
    Long::valueOf('4.4');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointNumberIsNotInteger() {
    Integer::valueOf(4.4);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointInStringIsNotInteger() {
    Integer::valueOf('4.4');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointNumberIsNotShort() {
    Short::valueOf(4.4);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointInStringIsNotShort() {
    Short::valueOf('4.4');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointNumberIsNotByte() {
    Byte::valueOf(4.4);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function floatingPointInStringIsNotByte() {
    Byte::valueOf('4.4');
  }
}
