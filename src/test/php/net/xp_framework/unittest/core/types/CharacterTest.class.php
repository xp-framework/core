<?php namespace net\xp_framework\unittest\core\types;

use lang\types\String;
use lang\types\Character;
use lang\types\Bytes;
use lang\FormatException;
use lang\IllegalArgumentException;

/**
 * TestCase
 *
 * @deprecated Wrapper types will move to their own library
 * @see  xp://lang.types.Character
 */
class CharacterTest extends \unittest\TestCase {

  #[@test, @expect(FormatException::class)]
  public function incompleteMultiByteCharacter() {
    new Character("\xe4", 'utf-8');
  }

  #[@test]
  public function nullByte() {
    $this->assertEquals(new Bytes("\x00"), (new Character(0))->getBytes());
  }

  #[@test]
  public function euroSymbol() {
    $this->assertEquals(new Bytes("\xe2\x82\xac"), (new Character(8364))->getBytes('utf-8')); // &#8364; in HTML
  }

  #[@test, @expect(FormatException::class)]
  public function illegalCharacter() {
    new Character("\xe4", 'US-ASCII');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function illegalLength() {
    new Character('ABC');
  }

  #[@test]
  public function usAsciiCharacter() {
    $this->assertEquals(new Bytes('H'), (new Character('H'))->getBytes());
  }

  #[@test]
  public function umlautCharacter() {
    $this->assertEquals(new Bytes("\303\244"), (new Character('ä', 'utf-8'))->getBytes('utf-8'));
  }

  #[@test]
  public function utf8Character() {
    $this->assertEquals(
      new Character('ä', 'utf-8'),
      new Character("\xe4", 'iso-8859-1')
    );
  }

  #[@test, @ignore('Does not work with all iconv implementations')]
  public function transliteration() {
    $this->assertEquals('c', (new String('Ä', 'utf-8'))->toString());
  }

  #[@test]
  public function worksWithEchoStatement() {
    ob_start();
    echo new Character('w');
    $this->assertEquals('w', ob_get_clean());
  }

  #[@test]
  public function stringCast() {
    $this->assertEquals('w', (string)new Character('w'));
  }

  #[@test]
  public function usedInStringFunction() {
    $this->assertEquals(
      'z', 
      str_replace('Z', 'z', new Character('Z')
    ));
  }
}
