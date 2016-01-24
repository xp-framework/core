<?php namespace net\xp_framework\unittest\util;

use util\Properties;
use util\Hashmap;
use util\CompositeProperties;
use unittest\actions\RuntimeVersion;
use lang\IllegalArgumentException;
use lang\Error;

/**
 * Test CompositeProperties
 *
 * @see   https://github.com/xp-framework/xp-framework/issues/302
 * @see    xp://util.CompositeProperies
 */
class CompositePropertiesTest extends \unittest\TestCase {

  #[@test]
  public function createCompositeSingle() {
    $c= new CompositeProperties([new Properties('')]);
    $this->assertEquals(1, $c->length());
  }

  #[@test]
  public function createCompositeDual() {
    $c= new CompositeProperties([new Properties('a.ini'), new Properties('b.ini')]);
    $this->assertEquals(2, $c->length());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function createCompositeThrowsExceptionWhenNoArgumentGiven() {
    new CompositeProperties();
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function createEmptyCompositeThrowsException() {
    new CompositeProperties([]);
  }

  #[@test, @expect(IllegalArgumentException::class), @action(new RuntimeVersion('<7.0.0-dev'))]]
  public function createCompositeThrowsExceptionWhenSomethingElseThenPropertiesGiven() {
    new CompositeProperties([new Properties(null), 1, new Properties(null)]);
  }

  #[@test, @expect(Error::class), @action(new RuntimeVersion('>=7.0.0-dev'))]]
  public function createCompositeThrowsExceptionWhenSomethingElseThenPropertiesGiven7() {
    new CompositeProperties([new Properties(null), 1, new Properties(null)]);
  }

  #[@test]
  public function addOtherProperties() {
    $c= new CompositeProperties([new Properties(null)]);
    $this->assertEquals(1, $c->length());

    $c->add(Properties::fromString('[section]'));
    $this->assertEquals(2, $c->length());
  }

  #[@test]
  public function addingIdenticalPropertiesIsIdempotent() {
    $p= new Properties('');
    $c= new CompositeProperties([$p]);
    $this->assertEquals(1, $c->length());

    $c->add($p);
    $this->assertEquals(1, $c->length());
  }

  #[@test]
  public function addingEqualPropertiesIsIdempotent() {
    $c= new CompositeProperties([Properties::fromString('[section]
a=b
b=c')]);
    $this->assertEquals(1, $c->length());

    $c->add(Properties::fromString('[section]
a=b
b=c'));
    $this->assertEquals(1, $c->length());
  }

  protected function fixture() {
    return new CompositeProperties([Properties::fromString('[section]
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
      Properties::fromString('[section]
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

  #[@test]
  public function readStringUsesFirstProperties() {
    $this->assertEquals('string...', $this->fixture()->readString('section', 'str'));
  }

  #[@test]
  public function readStringUsesSecondPropertiesWhenFirstEmpty() {
    $this->assertEquals('Another thing', $this->fixture()->readString('section', 'str2'));
  }

  #[@test]
  public function readStringReturnsDefaultOnNoOccurrance() {
    $this->assertEquals('Hello World', $this->fixture()->readString('section', 'non-existant-key', 'Hello World'));
  }

  #[@test]
  public function readStringDefaultForDefault() {
    $this->assertEquals('', $this->fixture()->readString('section', 'non-existant-key'));
  }

  #[@test]
  public function readBooleanUsesFirst() {
    $this->assertEquals(true, $this->fixture()->readBool('section', 'b1'));
  }

  #[@test]
  public function readBooleanUsesSecondIfFirstUnset() {
    $this->assertEquals(false, $this->fixture()->readBool('section', 'b2'));
  }

  #[@test]
  public function readBooleanUsesDefaultOnNoOccurrance() {
    $this->assertEquals(true, $this->fixture()->readBool('section', 'non-existant-key', true));
  }

  #[@test]
  public function readBooleanUsesFalseForDefaultOnNoOccurrance() {
    $this->assertEquals(false, $this->fixture()->readBool('section', 'b3'));
  }

  #[@test]
  public function readArrayUsesFirst() {
    $this->assertEquals(['foo', 'bar'], $this->fixture()->readArray('section', 'arr1'));
  }

  #[@test]
  public function readArrayUsesSecondIfFirstUnset() {
    $this->assertEquals(['foo', 'bar', 'baz'], $this->fixture()->readArray('section', 'arr2'));
  }

  #[@test]
  public function readArrayUsesDefaultOnNoOccurrance() {
    $this->assertEquals([1, 2, 3], $this->fixture()->readArray('section', 'non-existant-key', [1, 2, 3]));
  }

  #[@test]
  public function readArrayUsesEmptyArrayDefaultOnNoOccurrance() {
    $this->assertEquals([], $this->fixture()->readArray('section', 'non-existant-key'));
  }

  #[@test]
  public function readArrayDoesNotAddArrayElements() {
    $this->assertEquals(['foo'], $this->fixture()->readArray('section', 'arr3'));
  }

  #[@test]
  public function readMapUsesFirst() {
    $this->assertEquals(['a' => 'b', 'b' => 'c'], $this->fixture()->readMap('section', 'hash1'));
  }

  #[@test]
  public function readMapUsesSecondIfFirstUnset() {
    $this->assertEquals(['b' => 'null'], $this->fixture()->readMap('section', 'hash2'));
  }

  #[@test]
  public function readMapUsesDefaultOnNoOccurrance() {
    $this->assertEquals('Hello.', $this->fixture()->readMap('section', 'hash3', 'Hello.'));
  }

  #[@test]
  public function readMapUsesNullForDefaultOnNoOccurrance() {
    $this->assertEquals(null, $this->fixture()->readMap('section', 'hash3'));
  }

  #[@test]
  public function readIntegerUsesFirst() {
    $this->assertEquals(5, $this->fixture()->readInteger('section', 'int1'));
  }

  #[@test]
  public function readIntegerUsesSecondIfFirstUnset() {
    $this->assertEquals(4, $this->fixture()->readInteger('section', 'int2'));
  }

  #[@test]
  public function readIntegerUsesDefaultOnNoOccurrance() {
    $this->assertEquals(-1, $this->fixture()->readInteger('section', 'non-existant-key', -1));
  }

  #[@test]
  public function readIntegerUsesZeroForDefaultOnNoOccurrance() {
    $this->assertEquals(0, $this->fixture()->readInteger('section', 'non-existant-key'));
  }

  #[@test]
  public function readFloatUsesFirst() {
    $this->assertEquals(0.5, $this->fixture()->readFloat('section', 'float1'));
  }

  #[@test]
  public function readFloatUsesSecondIfFirstUnset() {
    $this->assertEquals(4.99999999, $this->fixture()->readFloat('section', 'float2'));
  }

  #[@test]
  public function readFloatUsesDefaultOnNoOccurrance() {
    $this->assertEquals(-1.0, $this->fixture()->readFloat('section', 'non-existant-key', -1.0));
  }

  #[@test]
  public function readFloatUsesZeroDefaultOnNoOccurrance() {
    $this->assertEquals(0.0, $this->fixture()->readFloat('section', 'non-existant-key'));
  }

  #[@test]
  public function readRangeUsesFirst() {
    $this->assertEquals([1, 2, 3], $this->fixture()->readRange('section', 'range1'));
  }

  #[@test]
  public function readRangeUsesSecondIfFirstUnset() {
    $this->assertEquals([99, 100], $this->fixture()->readRange('section', 'range2'));
  }

  #[@test]
  public function readRangeUsesDefaultOnNoOccurrance() {
    $this->assertEquals([1, 2, 3], $this->fixture()->readRange('section', 'non-existant-key', [1, 2, 3]));
  }

  #[@test]
  public function readRangeUsesEmptyArrayForDefaultOnNoOccurrance() {
    $this->assertEquals([], $this->fixture()->readRange('section', 'non-existant-key'));
  }

  #[@test]
  public function readSection() {
    $this->assertEquals(
      ['key' => 'value', 'anotherkey' => 'is there, too'],
      $this->fixture()->readSection('read')
    );
  }

  #[@test]
  public function readSectionThatDoesNotExistReturnsDefault() {
    $this->assertEquals(['default' => 'value'], $this->fixture()->readSection('doesnotexist', ['default' => 'value']));
  }

  #[@test]
  public function readSectionThatDoesNotExistReturnsEmptyArrayPerDefault() {
    $this->assertEquals([], $this->fixture()->readSection('doesnotexist'));
  }

  #[@test]
  public function readEmptySectionOverridesDefault() {
    $this->assertEquals([], $this->fixture()->readSection('empty', ['default' => 'value']));
  }

  #[@test]
  public function sectionFromMultipleSourcesExists() {
    $this->assertEquals(true, $this->fixture()->hasSection('section'));
  }

  #[@test]
  public function sectionFromSingleSourceExists() {
    $this->assertEquals(true, $this->fixture()->hasSection('secondsection'));
  }

  #[@test]
  public function nonexistantSectionDoesNotExist() {
    $this->assertEquals(false, $this->fixture()->hasSection('any'));
  }

  #[@test]
  public function getFirstSection() {
    $fixture= $this->fixture();
    $this->assertEquals('section', $fixture->getFirstSection());
    return $fixture;
  }

  #[@test]
  public function getNextSection() {
    $fixture= $this->getFirstSection();
    $this->assertEquals('read', $fixture->getNextSection());
    return $fixture;
  }

  #[@test]
  public function getThirdSection() {
    $fixture= $this->getNextSection();
    $this->assertEquals('secondsection', $fixture->getNextSection());
    return $fixture;
  }

  #[@test]
  public function nextSectionCannotBeCalledWithoutCallToFirstSection() {
    $this->assertEquals(null, $this->fixture()->getNextSection());
  }

  #[@test]
  public function getLastSectionReturnsNull() {
    $fixture= $this->getThirdSection();
    $this->assertEquals('empty', $fixture->getNextSection());
    $this->assertEquals(null, $fixture->getNextSection());
    return $fixture;
  }

  #[@test]
  public function lastSectionReturnsNullForever() {
    $this->assertEquals(null, $this->getLastSectionReturnsNull()->getNextSection());
  }

  #[@test]
  public function firstSectionResetsLoop() {
    $fixture= $this->getThirdSection();
    $this->assertEquals('section', $fixture->getFirstSection());
    $this->assertEquals('read', $fixture->getNextSection());
  }

  #[@test]
  public function addingToCompositeResetsIterationPointer() {
    $fixture= $this->getThirdSection();
    $fixture->add(Properties::fromString('[unknown]'));

    $this->assertEquals(null, $fixture->getNextSection());
  }
}
