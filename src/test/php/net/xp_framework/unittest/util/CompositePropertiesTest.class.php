<?php namespace net\xp_framework\unittest\util;

use lang\{Error, IllegalArgumentException};
use unittest\actions\RuntimeVersion;
use unittest\{Expect, Test, TestCase};
use util\{CompositeProperties, Hashmap, Properties};

/**
 * Test CompositeProperties
 *
 * @see   https://github.com/xp-framework/xp-framework/issues/302
 * @see    xp://util.CompositeProperies
 */
class CompositePropertiesTest extends TestCase {

  #[Test]
  public function createCompositeSingle() {
    $c= new CompositeProperties([new Properties('')]);
    $this->assertEquals(1, $c->length());
  }

  #[Test]
  public function createCompositeDual() {
    $c= new CompositeProperties([new Properties('a.ini'), new Properties('b.ini')]);
    $this->assertEquals(2, $c->length());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function createCompositeThrowsExceptionWhenNoArgumentGiven() {
    new CompositeProperties();
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function createEmptyCompositeThrowsException() {
    new CompositeProperties([]);
  }

  #[Test, Expect(Error::class)]
  public function createCompositeThrowsExceptionWhenSomethingElseThenPropertiesGiven() {
    new CompositeProperties([new Properties(null), 1, new Properties(null)]);
  }

  #[Test]
  public function addOtherProperties() {
    $c= new CompositeProperties([new Properties(null)]);
    $this->assertEquals(1, $c->length());

    $c->add(new Properties('a.ini'));
    $this->assertEquals(2, $c->length());
  }

  #[Test]
  public function addingIdenticalPropertiesIsIdempotent() {
    $p= new Properties('');
    $c= new CompositeProperties([$p]);
    $this->assertEquals(1, $c->length());

    $c->add($p);
    $this->assertEquals(1, $c->length());
  }

  #[Test]
  public function addingEqualPropertiesIsIdempotent() {
    $c= new CompositeProperties([(new Properties())->load("[section]\na=b\nb=c")]);
    $this->assertEquals(1, $c->length());

    $c->add((new Properties())->load("[section]\na=b\nb=c"));
    $this->assertEquals(1, $c->length());
  }

  protected function fixture() {
    return new CompositeProperties([(new Properties())->load('[section]
str="string..."
b1=true
arr1="foo|bar"
arr3="foo"
hash1="a:b|b:c"
int1=5
float1=0.5
range1=1..3

[read]
key=value'),
      (new Properties())->load('[section]
str="Another thing"
str2="Another thing"
b1=false
b2=false
arr1="foo|bar|baz"
arr2="foo|bar|baz"
arr3[]="bar"
hash1="b:a|c:b"
hash2="b:null"
int1=10
int2=4
float1=1.1
float2=4.99999999
range1=5..6
range2=99..100

[secondsection]
foo=bar

[read]
key="This must not appear, as first has precedence"
anotherkey="is there, too"

[empty]
')]);

    return $c;
  }

  #[Test]
  public function readStringUsesFirstProperties() {
    $this->assertEquals('string...', $this->fixture()->readString('section', 'str'));
  }

  #[Test]
  public function readStringUsesSecondPropertiesWhenFirstEmpty() {
    $this->assertEquals('Another thing', $this->fixture()->readString('section', 'str2'));
  }

  #[Test]
  public function readStringReturnsDefaultOnNoOccurrance() {
    $this->assertEquals('Hello World', $this->fixture()->readString('section', 'non-existant-key', 'Hello World'));
  }

  #[Test]
  public function readStringDefaultForDefault() {
    $this->assertEquals('', $this->fixture()->readString('section', 'non-existant-key'));
  }

  #[Test]
  public function readBooleanUsesFirst() {
    $this->assertEquals(true, $this->fixture()->readBool('section', 'b1'));
  }

  #[Test]
  public function readBooleanUsesSecondIfFirstUnset() {
    $this->assertEquals(false, $this->fixture()->readBool('section', 'b2'));
  }

  #[Test]
  public function readBooleanUsesDefaultOnNoOccurrance() {
    $this->assertEquals(true, $this->fixture()->readBool('section', 'non-existant-key', true));
  }

  #[Test]
  public function readBooleanUsesFalseForDefaultOnNoOccurrance() {
    $this->assertEquals(false, $this->fixture()->readBool('section', 'b3'));
  }

  #[Test]
  public function readArrayUsesFirst() {
    $this->assertEquals(['foo', 'bar'], $this->fixture()->readArray('section', 'arr1'));
  }

  #[Test]
  public function readArrayUsesSecondIfFirstUnset() {
    $this->assertEquals(['foo', 'bar', 'baz'], $this->fixture()->readArray('section', 'arr2'));
  }

  #[Test]
  public function readArrayUsesDefaultOnNoOccurrance() {
    $this->assertEquals([1, 2, 3], $this->fixture()->readArray('section', 'non-existant-key', [1, 2, 3]));
  }

  #[Test]
  public function readArrayUsesEmptyArrayDefaultOnNoOccurrance() {
    $this->assertEquals([], $this->fixture()->readArray('section', 'non-existant-key'));
  }

  #[Test]
  public function readArrayDoesNotAddArrayElements() {
    $this->assertEquals(['foo'], $this->fixture()->readArray('section', 'arr3'));
  }

  #[Test]
  public function readMapUsesFirst() {
    $this->assertEquals(['a' => 'b', 'b' => 'c'], $this->fixture()->readMap('section', 'hash1'));
  }

  #[Test]
  public function readMapUsesSecondIfFirstUnset() {
    $this->assertEquals(['b' => 'null'], $this->fixture()->readMap('section', 'hash2'));
  }

  #[Test]
  public function readMapUsesDefaultOnNoOccurrance() {
    $this->assertEquals('Hello.', $this->fixture()->readMap('section', 'hash3', 'Hello.'));
  }

  #[Test]
  public function readMapUsesNullForDefaultOnNoOccurrance() {
    $this->assertEquals(null, $this->fixture()->readMap('section', 'hash3'));
  }

  #[Test]
  public function readIntegerUsesFirst() {
    $this->assertEquals(5, $this->fixture()->readInteger('section', 'int1'));
  }

  #[Test]
  public function readIntegerUsesSecondIfFirstUnset() {
    $this->assertEquals(4, $this->fixture()->readInteger('section', 'int2'));
  }

  #[Test]
  public function readIntegerUsesDefaultOnNoOccurrance() {
    $this->assertEquals(-1, $this->fixture()->readInteger('section', 'non-existant-key', -1));
  }

  #[Test]
  public function readIntegerUsesZeroForDefaultOnNoOccurrance() {
    $this->assertEquals(0, $this->fixture()->readInteger('section', 'non-existant-key'));
  }

  #[Test]
  public function readFloatUsesFirst() {
    $this->assertEquals(0.5, $this->fixture()->readFloat('section', 'float1'));
  }

  #[Test]
  public function readFloatUsesSecondIfFirstUnset() {
    $this->assertEquals(4.99999999, $this->fixture()->readFloat('section', 'float2'));
  }

  #[Test]
  public function readFloatUsesDefaultOnNoOccurrance() {
    $this->assertEquals(-1.0, $this->fixture()->readFloat('section', 'non-existant-key', -1.0));
  }

  #[Test]
  public function readFloatUsesZeroDefaultOnNoOccurrance() {
    $this->assertEquals(0.0, $this->fixture()->readFloat('section', 'non-existant-key'));
  }

  #[Test]
  public function readRangeUsesFirst() {
    $this->assertEquals([1, 2, 3], $this->fixture()->readRange('section', 'range1'));
  }

  #[Test]
  public function readRangeUsesSecondIfFirstUnset() {
    $this->assertEquals([99, 100], $this->fixture()->readRange('section', 'range2'));
  }

  #[Test]
  public function readRangeUsesDefaultOnNoOccurrance() {
    $this->assertEquals([1, 2, 3], $this->fixture()->readRange('section', 'non-existant-key', [1, 2, 3]));
  }

  #[Test]
  public function readRangeUsesEmptyArrayForDefaultOnNoOccurrance() {
    $this->assertEquals([], $this->fixture()->readRange('section', 'non-existant-key'));
  }

  #[Test]
  public function readSection() {
    $this->assertEquals(
      ['key' => 'value', 'anotherkey' => 'is there, too'],
      $this->fixture()->readSection('read')
    );
  }

  #[Test]
  public function readSectionThatDoesNotExistReturnsDefault() {
    $this->assertEquals(['default' => 'value'], $this->fixture()->readSection('doesnotexist', ['default' => 'value']));
  }

  #[Test]
  public function readSectionThatDoesNotExistReturnsEmptyArrayPerDefault() {
    $this->assertEquals([], $this->fixture()->readSection('doesnotexist'));
  }

  #[Test]
  public function readEmptySectionOverridesDefault() {
    $this->assertEquals([], $this->fixture()->readSection('empty', ['default' => 'value']));
  }

  #[Test]
  public function sectionFromMultipleSourcesExists() {
    $this->assertEquals(true, $this->fixture()->hasSection('section'));
  }

  #[Test]
  public function sectionFromSingleSourceExists() {
    $this->assertEquals(true, $this->fixture()->hasSection('secondsection'));
  }

  #[Test]
  public function nonexistantSectionDoesNotExist() {
    $this->assertEquals(false, $this->fixture()->hasSection('any'));
  }
}