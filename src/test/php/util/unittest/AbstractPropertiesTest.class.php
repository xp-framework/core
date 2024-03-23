<?php namespace util\unittest;

use lang\{ElementNotFoundException, FormatException, IllegalStateException};
use test\{Assert, Expect, Test, Values};
use util\Properties;

abstract class AbstractPropertiesTest {

  /** Create a new properties object from a string source */
  protected abstract function newPropertiesFrom(string $source): Properties;

  /**
   * Gets a fixture
   *
   * @param   string $section They key/value pairs inside the section
   * @return  util.Properties
   */
  protected function fixture($section) {
    return $this->newPropertiesFrom("[section]\n".$section);
  }

  #[Test]
  public function can_create_from_empty_string() {
    $this->newPropertiesFrom('');
  }

  #[Test, Values(['key="value"', 'key=value'])]
  public function read_string($section) {
    Assert::equals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[Test, Values(['key=""', 'key='])]
  public function read_empty_string($section) {
    Assert::equals('', $this->fixture($section)->readString('section', 'key'));
  }

  #[Test]
  public function readString_returns_default_for_non_existant_key() {
    Assert::equals('(Default)', $this->fixture('')->readString('section', 'non-existant', '(Default)'));
  }

  #[Test, Values(['key=2', 'key=2.0', 'key="2"'])]
  public function read_int($section) {
    Assert::equals(2, $this->fixture($section)->readInteger('section', 'key'));
  }

  #[Test]
  public function readInteger_returns_default_for_non_existant_key() {
    Assert::equals(0xFFFF, $this->fixture('')->readInteger('section', 'non-existant', 0xFFFF));
  }

  #[Test, Values(['key=2.0', 'key=2', 'key="2"'])]
  public function read_float($section) {
    Assert::equals(2.0, $this->fixture($section)->readFloat('section', 'key'));
  }

  #[Test]
  public function readFloat_returns_default_for_non_existant_key() {
    Assert::equals(61.0, $this->fixture('')->readFloat('section', 'non-existant', 61.0));
  }

  #[Test, Values(['key=0', 'key=off', 'key=false', 'key=no'])]
  public function read_bool_false($section) {
    Assert::false($this->fixture($section)->readBool('section', 'key'));
  }

  #[Test, Values(['key=1', 'key=on', 'key=true', 'key=yes'])]
  public function read_bool_true($section) {
    Assert::true($this->fixture($section)->readBool('section', 'key'));
  }

  #[Test]
  public function readBool_returns_default_for_non_existant_key() {
    Assert::null($this->fixture('')->readBool('section', 'non-existant', null));
  }

  #[Test, Values(['key=1..3', 'key="1..3"'])]
  public function read_range($section) {
    Assert::equals([1, 2, 3], $this->fixture($section)->readRange('section', 'key'));
  }

  #[Test, Values(['key=-3..-1', 'key="-3..-1"'])]
  public function read_range_with_negative_numbers($section) {
    Assert::equals([-3, -2, -1], $this->fixture($section)->readRange('section', 'key'));
  }

  #[Test, Values(['key=3..1', 'key="3..1"'])]
  public function read_range_backwards($section) {
    Assert::equals([3, 2, 1], $this->fixture($section)->readRange('section', 'key'));
  }

  #[Test, Values(['key=""', 'key='])]
  public function read_empty_range($section) {
    Assert::equals([], $this->fixture($section)->readRange('section', 'key'));
  }

  #[Test]
  public function readRange_returns_default_for_non_existant_key() {
    Assert::equals([1, 2, 3], $this->fixture('')->readRange('section', 'non-existant', [1, 2, 3]));
  }

  #[Test, Values(['key[]=value', 'key="value"', 'key=value'])]
  public function read_array_with_one_element($section) {
    Assert::equals(['value'], $this->fixture($section)->readArray('section', 'key'));
  }

  #[Test, Values(["key[]=a\nkey[]=b\nkey[]=c", 'key="a|b|c"'])]
  public function read_array($section) {
    Assert::equals(['a', 'b', 'c'], $this->fixture($section)->readArray('section', 'key'));
  }

  #[Test, Values(['key=""', 'key='])]
  public function read_empty_array($section) {
    Assert::equals([], $this->fixture($section)->readArray('section', 'key'));
  }

  #[Test]
  public function readArray_returns_default_for_non_existant_key() {
    Assert::equals([1, 2, 3], $this->fixture('')->readFloat('section', 'non-existant', [1, 2, 3]));
  }

  #[Test, Values(['key[k]=value', 'key="k:value"'])]
  public function read_map_with_one_element($section) {
    Assert::equals(['k' => 'value'], $this->fixture($section)->readMap('section', 'key'));
  }

  #[Test, Values(["key[a]=1\nkey[b]=2\nkey[c]=3", 'key="a:1|b:2|c:3"'])]
  public function read_map($section) {
    Assert::equals(['a' => '1', 'b' => '2', 'c' => '3'], $this->fixture($section)->readMap('section', 'key'));
  }

  #[Test, Values(['key=""', 'key='])]
  public function read_empty_map($section) {
    Assert::equals([], $this->fixture($section)->readMap('section', 'key'));
  }

  #[Test]
  public function readMap_returns_default_for_non_existant_key() {
    Assert::equals(['key' => 'value'], $this->fixture('')->readFloat('section', 'non-existant', ['key' => 'value']));
  }

  #[Test]
  public function read_section() {
    Assert::equals(['key' => 'value'], $this->fixture('key=value')->readSection('section'));
  }

  #[Test]
  public function read_empty_section() {
    Assert::equals([], $this->fixture('')->readSection('section'));
  }

  #[Test]
  public function readSection_returns_default_for_non_existant_key() {
    Assert::equals(['default' => 'value'], $this->fixture('')->readSection('non-existant', ['default' => 'value']));
  }

  #[Test, Values(['key=value    ; A comment', 'key="value"  ; A comment'])]
  public function comment_at_end_of_line_ignored($section) {
    Assert::equals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[Test]
  public function semicolon_inside_quoted_string_does_not_become_a_comment() {
    Assert::equals('value ; no comment', $this->fixture('key="value ; no comment"')->readString('section', 'key'));
  }

  #[Test, Values([' [section]', '[section]', ' [section] '])]
  public function sections_can_be_surrounded_by_whitespace($source) {
    Assert::true($this->newPropertiesFrom($source)->hasSection('section'));
  }

  #[Test, Values([' key=value', 'key =value', ' key =value'])]
  public function keys_can_be_surrounded_by_whitespace($section) {
    Assert::equals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[Test, Values(['key=  value  ', 'key=value  ', 'key=  value'])]
  public function unquoted_values_are_trimmed($section) {
    Assert::equals('value', $this->fixture($section)->readString('section', 'key'));
  }

  #[Test]
  public function quoted_values_are_not_trimmed() {
    Assert::equals('  value  ', $this->fixture('key="  value  "')->readString('section', 'key'));
  }

  #[Test]
  public function quoted_strings_can_span_multiple_lines() {
    Assert::equals("\nfirst\nsecond\nthird", $this->fixture("key=\"\nfirst\nsecond\nthird\"")->readString('section', 'key'));
  }

  #[Test]
  public function whitespace_is_relevant_in_multiline_strings() {
    Assert::equals("value  \n   value ", $this->fixture("key=\"value  \n   value \"")->readString('section', 'key'));
  }

  #[Test, Values(['key=value', ''])]
  public function has_section_with($section) {
    Assert::true($this->fixture($section)->hasSection('section'));
  }

  #[Test]
  public function does_not_have_non_existant_section() {
    Assert::false($this->fixture('')->hasSection('nonexistant'));
  }

  #[Test]
  public function iterate_sections() {
    $p= $this->newPropertiesFrom('
      [section]
      foo=bar

      [next]
      foo=bar

      [empty]

      [final]
      foo=bar
    ');

    Assert::equals(['section', 'next', 'empty', 'final'], [...$p->sections()]);
  }

  #[Test, Expect(FormatException::class), Values([["[section]\nfoo", 'missing equals sign for key'], ["[section]\nfoo]=value", 'key contains unbalanced bracket'], ["[section\nfoo=bar", 'section missing closing bracket']])]
  public function malformed_property_file($source) {
    $this->newPropertiesFrom($source);
  }

  #[Test]
  public function utf8_by_default() {
    $p= $this->newPropertiesFrom(
      "[section]\n".
      "key=Übercoder",
      'utf-8'
    );
    Assert::equals('Übercoder', $p->readString('section', 'key'));
  }

  #[Test]
  public function honors_utf8_BOM() {
    $p= $this->newPropertiesFrom(
      "\357\273\277".
      "[section]\n".
      "key=Übercoder"
    );
    Assert::equals('Übercoder', $p->readString('section', 'key'));
  }

  #[Test]
  public function honors_utf16BE_BOM() {
    $p= $this->newPropertiesFrom(
      "\376\377".
      "\0[\0s\0e\0c\0t\0i\0o\0n\0]\0\n".
      "\0k\0e\0y\0=\0\xdc\0b\0e\0r\0c\0o\0d\0e\0r\0\n"
    );
    Assert::equals('Übercoder', $p->readString('section', 'key'));
  }

  #[Test]
  public function honors_utf16LE_BOM() {
    $p= $this->newPropertiesFrom(
      "\377\376".
      "[\0s\0e\0c\0t\0i\0o\0n\0]\0\n\0".
      "k\0e\0y\0=\0\xdc\0b\0e\0r\0c\0o\0d\0e\0r\0\n\0"
    );
    Assert::equals('Übercoder', $p->readString('section', 'key'));
  }

  #[Test]
  public function remove_existant_section() {
    $p= $this->fixture('');
    $p->removeSection('section');
    Assert::false($p->hasSection('section'));
  }

  #[Test, Expect(IllegalStateException::class)]
  public function remove_non_existant_section() {
    $this->fixture('')->removeSection('non-existant');
  }

  #[Test]
  public function remove_existant_key() {
    $p= $this->fixture('key=value');
    $p->removeKey('section', 'key');
    Assert::null($p->readString('section', 'key', null));
  }

  #[Test, Expect(IllegalStateException::class), Values(['section', 'non-existant'])]
  public function remove_non_existant_key($section) {
    $this->fixture('key=value')->removeKey($section, 'non-existant');
  }

  #[Test, Values([['', '', 'empty properties'], ["[section]", "[section]", 'with one empty section'], ["[section]\nkey=value", "[section]\nkey=value", 'with one non-empty section'], ["[a]\ncolor=red\n[b]\ncolor=green", "[a]\ncolor=red\n[b]\ncolor=green", 'with two sections'], ["[a]\ncolor=red\n[b]\ncolor=green", "[b]\ncolor=green\n[a]\ncolor=red", 'with two sections in different order']])]
  public function equals_other_properties_with_same_keys_and_values($a, $b) {
    Assert::equals($this->newPropertiesFrom($a), $this->newPropertiesFrom($b));
  }

  #[Test, Values([["[section]", 'with one empty section'], ["[section]\nkey=value", 'with one non-empty section'], ["[a]\ncolor=red\n[b]\ncolor=green", 'with two sections']])]
  public function empty_properties_not_equal_to_non_empty($source) {
    Assert::notEquals($this->newPropertiesFrom(''), $this->newPropertiesFrom($source));
  }

  #[Test, Values([["[section]", 'with one empty section'], ["[section]\nkey=value", 'with one non-empty section'], ["[a]\ncolor=red\n[b]\ncolor=green", 'with two sections']])]
  public function different_properties_not_equal_to_non_empty($source) {
    Assert::notEquals($this->newPropertiesFrom("[section]\ndifferent=value"), $this->newPropertiesFrom($source));
  }

  #[Test, Expect(FormatException::class)]
  public function resolve_unsupported_type() {
    $this->fixture('test=${not.supported}')->readString('section', 'test');
  }

  #[Test]
  public function resolve_environment_variable() {
    putenv('TEST=this');
    $value= $this->fixture('test=${env.TEST}')->readString('section', 'test');
    putenv('TEST');
    Assert::equals('this', $value);
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function resolve_non_existant_environment_variable() {
    putenv('TEST');
    $this->fixture('test=${env.TEST}')->readString('section', 'test');
  }

  #[Test]
  public function resolve_non_existant_senvironment_variable_with_default() {
    putenv('TEST');
    $value= $this->fixture('test=${env.TEST|this}')->readString('section', 'test');
    Assert::equals('this', $value);
  }

  #[Test]
  public function resolve_with_sections() {
    putenv('TEST=this');
    $value= $this->fixture('test=${env.TEST}')->readSection('section');
    putenv('TEST');
    Assert::equals(['test' => 'this'], $value);
  }

  #[Test]
  public function resolve_with_arrays() {
    putenv('TEST=this');
    $value= $this->fixture('test[]=${env.TEST}')->readArray('section', 'test');
    putenv('TEST');
    Assert::equals(['this'], $value);
  }

  #[Test]
  public function resolve_with_maps() {
    putenv('TEST=this');
    $value= $this->fixture('test[key]=${env.TEST}')->readMap('section', 'test');
    putenv('TEST');
    Assert::equals(['key' => 'this'], $value);
  }

  #[Test]
  public function compare_by_data() {
    $p1= $this->fixture('key=value');
    $p2= $this->fixture('key=value');
    $p3= $this->fixture('test=this');

    Assert::equals($p1, $p2);
    Assert::notEquals($p1, $p3);
  }
}