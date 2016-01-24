<?php namespace net\xp_framework\unittest\util;

use util\Properties;
use util\Hashmap;
use lang\FormatException;
use lang\IllegalStateException;
use lang\ElementNotFoundException;

/**
 * Testcase for util.Properties class.
 *
 * @see      xp://util.Properties
 */
abstract class AbstractPropertiesTest extends \unittest\TestCase {

  /**
   * Create a new properties object from a string source
   *
   * @param   string $source
   * @return  util.Properties
   */
  protected abstract function newPropertiesFrom($source);

  /**
   * Gets a fixture
   *
   * @param   string $section They key/value pairs inside the section
   * @return  util.Properties
   */
  protected function fixture($section) {
    return $this->newPropertiesFrom("[section]\n".$section);
  }

  #[@test]
  public function can_create_from_empty_string() {
    $this->newPropertiesFrom('');
  }

  #[@test, @values(['key="value"', 'key=value'])]
  public function read_string($section) {
    $this->assertEquals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[@test, @values(['key=""', 'key='])]
  public function read_empty_string($section) {
    $this->assertEquals('', $this->fixture($section)->readString('section', 'key'));
  }

  #[@test]
  public function readString_returns_default_for_non_existant_key() {
    $this->assertEquals('(Default)', $this->fixture('')->readString('section', 'non-existant', '(Default)'));
  }

  #[@test, @values(['key=2', 'key=2.0', 'key="2"'])]
  public function read_int($section) {
    $this->assertEquals(2, $this->fixture($section)->readInteger('section', 'key'));
  }

  #[@test]
  public function readInteger_returns_default_for_non_existant_key() {
    $this->assertEquals(0xFFFF, $this->fixture('')->readInteger('section', 'non-existant', 0xFFFF));
  }

  #[@test, @values(['key=2.0', 'key=2', 'key="2"'])]
  public function read_float($section) {
    $this->assertEquals(2.0, $this->fixture($section)->readFloat('section', 'key'));
  }

  #[@test]
  public function readFloat_returns_default_for_non_existant_key() {
    $this->assertEquals(61.0, $this->fixture('')->readFloat('section', 'non-existant', 61.0));
  }

  #[@test, @values(['key=0', 'key=off', 'key=false', 'key=no'])]
  public function read_bool_false($section) {
    $this->assertFalse($this->fixture($section)->readBool('section', 'key'));
  }

  #[@test, @values(['key=1', 'key=on', 'key=true', 'key=yes'])]
  public function read_bool_true($section) {
    $this->assertTrue($this->fixture($section)->readBool('section', 'key'));
  }

  #[@test]
  public function readBool_returns_default_for_non_existant_key() {
    $this->assertNull($this->fixture('')->readBool('section', 'non-existant', null));
  }

  #[@test, @values(['key=1..3', 'key="1..3"'])]
  public function read_range($section) {
    $this->assertEquals([1, 2, 3], $this->fixture($section)->readRange('section', 'key'));
  }

  #[@test, @values(['key=-3..-1', 'key="-3..-1"'])]
  public function read_range_with_negative_numbers($section) {
    $this->assertEquals([-3, -2, -1], $this->fixture($section)->readRange('section', 'key'));
  }

  #[@test, @values(['key=3..1', 'key="3..1"'])]
  public function read_range_backwards($section) {
    $this->assertEquals([3, 2, 1], $this->fixture($section)->readRange('section', 'key'));
  }

  #[@test, @values(['key=""', 'key='])]
  public function read_empty_range($section) {
    $this->assertEquals([], $this->fixture($section)->readRange('section', 'key'));
  }

  #[@test]
  public function readRange_returns_default_for_non_existant_key() {
    $this->assertEquals([1, 2, 3], $this->fixture('')->readRange('section', 'non-existant', [1, 2, 3]));
  }

  #[@test, @values(['key[]=value', 'key="value"', 'key=value'])]
  public function read_array_with_one_element($section) {
    $this->assertEquals(['value'], $this->fixture($section)->readArray('section', 'key'));
  }

  #[@test, @values(["key[]=a\nkey[]=b\nkey[]=c", 'key="a|b|c"'])]
  public function read_array($section) {
    $this->assertEquals(['a', 'b', 'c'], $this->fixture($section)->readArray('section', 'key'));
  }

  #[@test, @values(['key=""', 'key='])]
  public function read_empty_array($section) {
    $this->assertEquals([], $this->fixture($section)->readArray('section', 'key'));
  }

  #[@test]
  public function readArray_returns_default_for_non_existant_key() {
    $this->assertEquals([1, 2, 3], $this->fixture('')->readFloat('section', 'non-existant', [1, 2, 3]));
  }

  #[@test, @values(['key[k]=value', 'key="k:value"'])]
  public function read_map_with_one_element($section) {
    $this->assertEquals(['k' => 'value'], $this->fixture($section)->readMap('section', 'key'));
  }

  #[@test, @values(["key[a]=1\nkey[b]=2\nkey[c]=3", 'key="a:1|b:2|c:3"'])]
  public function read_map($section) {
    $this->assertEquals(['a' => '1', 'b' => '2', 'c' => '3'], $this->fixture($section)->readMap('section', 'key'));
  }

  #[@test, @values(['key=""', 'key='])]
  public function read_empty_map($section) {
    $this->assertEquals([], $this->fixture($section)->readMap('section', 'key'));
  }

  #[@test]
  public function readMap_returns_default_for_non_existant_key() {
    $this->assertEquals(['key' => 'value'], $this->fixture('')->readFloat('section', 'non-existant', ['key' => 'value']));
  }

  #[@test]
  public function read_section() {
    $this->assertEquals(['key' => 'value'], $this->fixture('key=value')->readSection('section'));
  }

  #[@test]
  public function read_empty_section() {
    $this->assertEquals([], $this->fixture('')->readSection('section'));
  }

  #[@test]
  public function readSection_returns_default_for_non_existant_key() {
    $this->assertEquals(['default' => 'value'], $this->fixture('')->readSection('non-existant', ['default' => 'value']));
  }

  #[@test, @values([
  #  'key=value    ; A comment',
  #  'key="value"  ; A comment'
  #])]
  public function comment_at_end_of_line_ignored($section) {
    $this->assertEquals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[@test]
  public function semicolon_inside_quoted_string_does_not_become_a_comment() {
    $this->assertEquals('value ; no comment', $this->fixture('key="value ; no comment"')->readString('section', 'key'));
  }

  #[@test, @values([
  #  ' [section]',
  #  '[section]',
  #  ' [section] '
  #])]
  public function sections_can_be_surrounded_by_whitespace($source) {
    $this->assertTrue($this->newPropertiesFrom($source)->hasSection('section'));
  }

  #[@test, @values([
  #  ' key=value',
  #  'key =value',
  #  ' key =value'
  #])]
  public function keys_can_be_surrounded_by_whitespace($section) {
    $this->assertEquals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[@test, @values([
  #  'key=  value  ',
  #  'key=value  ',
  #  'key=  value'
  #])]
  public function unquoted_values_are_trimmed($section) {
    $this->assertEquals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[@test]
  public function quoted_values_are_not_trimmed() {
    $this->assertEquals('  value  ', $this->fixture('key="  value  "')->readString('section', 'key'));
  }

  #[@test]
  public function quoted_strings_can_span_multiple_lines() {
    $this->assertEquals("\nfirst\nsecond\nthird", $this->fixture("key=\"\nfirst\nsecond\nthird\"")->readString('section', 'key'));
  }

  #[@test]
  public function whitespace_is_relevant_in_multiline_strings() {
    $this->assertEquals("value  \n   value ", $this->fixture("key=\"value  \n   value \"")->readString('section', 'key'));
  }

  #[@test, @values(['key=value', ''])]
  public function has_section_with($section) {
    $this->assertTrue($this->fixture($section)->hasSection('section'));
  }

  #[@test]
  public function does_not_have_non_existant_section() {
    $this->assertFalse($this->fixture('')->hasSection('nonexistant'));
  }

  #[@test]
  public function iterate_sections_with_first_and_next() {
    $p= $this->newPropertiesFrom('
      [section]
      foo=bar

      [next]
      foo=bar

      [empty]

      [final]
      foo=bar
    ');
    
    $this->assertEquals('section', $p->getFirstSection());
    $this->assertEquals('next', $p->getNextSection());
    $this->assertEquals('empty', $p->getNextSection());     
    $this->assertEquals('final', $p->getNextSection());
  }

  #[@test, @expect(FormatException::class), @values([
  #  ["[section]\nfoo", 'missing equals sign for key'],
  #  ["[section]\nfoo]=value", 'key contains unbalanced bracket'],
  #  ["[section\nfoo=bar", 'section missing closing bracket']
  #])]
  public function malformed_property_file($source) {
    $this->newPropertiesFrom($source);
  }

  #[@test]
  public function honors_utf8_BOM() {
    $p= $this->newPropertiesFrom(
      "\357\273\277".
      "[section]\n".
      "key=Übercoder"
    );
    $this->assertEquals('Übercoder', $p->readString('section', 'key'));
  }

  #[@test]
  public function honors_utf16BE_BOM() {
    $p= $this->newPropertiesFrom(
      "\376\377".
      "\0[\0s\0e\0c\0t\0i\0o\0n\0]\0\n".
      "\0k\0e\0y\0=\0\xdc\0b\0e\0r\0c\0o\0d\0e\0r\0\n"
    );
    $this->assertEquals('Übercoder', $p->readString('section', 'key'));
  }

  #[@test]
  public function honors_utf16LE_BOM() {
    $p= $this->newPropertiesFrom(
      "\377\376".
      "[\0s\0e\0c\0t\0i\0o\0n\0]\0\n\0".
      "k\0e\0y\0=\0\xdc\0b\0e\0r\0c\0o\0d\0e\0r\0\n\0"
    );
    $this->assertEquals('Übercoder', $p->readString('section', 'key'));
  }

  #[@test]
  public function remove_existant_section() {
    $p= $this->fixture('');
    $p->removeSection('section');
    $this->assertFalse($p->hasSection('section'));
  }

  #[@test, @expect(IllegalStateException::class)]
  public function remove_non_existant_section() {
    $this->fixture('')->removeSection('non-existant');
  }

  #[@test]
  public function remove_existant_key() {
    $p= $this->fixture('key=value');
    $p->removeKey('section', 'key');
    $this->assertNull($p->readString('section', 'key', null));
  }

  #[@test, @expect(IllegalStateException::class), @values(['section', 'non-existant'])]
  public function remove_non_existant_key($section) {
    $this->fixture('key=value')->removeKey($section, 'non-existant');
  }

  #[@test, @values([
  #  ['', '', 'empty properties'],
  #  ["[section]", "[section]", 'with one empty section'],
  #  ["[section]\nkey=value", "[section]\nkey=value", 'with one non-empty section'],
  #  ["[a]\ncolor=red\n[b]\ncolor=green", "[a]\ncolor=red\n[b]\ncolor=green", 'with two sections'],
  #  ["[a]\ncolor=red\n[b]\ncolor=green", "[b]\ncolor=green\n[a]\ncolor=red", 'with two sections in different order']
  #])]
  public function equals_other_properties_with_same_keys_and_values($a, $b) {
    $this->assertEquals($this->newPropertiesFrom($a), $this->newPropertiesFrom($b));
  }

  #[@test, @values([
  #  ["[section]", 'with one empty section'],
  #  ["[section]\nkey=value", 'with one non-empty section'],
  #  ["[a]\ncolor=red\n[b]\ncolor=green", 'with two sections']
  #])]
  public function empty_properties_not_equal_to_non_empty($source) {
    $this->assertNotEquals($this->newPropertiesFrom(''), $this->newPropertiesFrom($source));
  }

  #[@test, @values([
  #  ["[section]", 'with one empty section'],
  #  ["[section]\nkey=value", 'with one non-empty section'],
  #  ["[a]\ncolor=red\n[b]\ncolor=green", 'with two sections']
  #])]
  public function different_properties_not_equal_to_non_empty($source) {
    $this->assertNotEquals($this->newPropertiesFrom("[section]\ndifferent=value"), $this->newPropertiesFrom($source));
  }

  #[@test, @expect(FormatException::class)]
  public function resolve_unsupported_type() {
    $this->fixture('test=${not.supported}');
  }

  #[@test]
  public function resolve_environment_variable() {
    putenv('TEST=this');
    $value= $this->fixture('test=${env.TEST}')->readString('section', 'test');
    putenv('TEST');
    $this->assertEquals('this', $value);
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function resolve_non_existant_senvironment_variable() {
    putenv('TEST');
    $this->fixture('test=${env.TEST}');
  }

  #[@test]
  public function resolve_non_existant_senvironment_variable_with_default() {
    putenv('TEST');
    $value= $this->fixture('test=${env.TEST|this}')->readString('section', 'test');
    $this->assertEquals('this', $value);
  }
}
