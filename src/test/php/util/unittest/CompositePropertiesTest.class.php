<?php namespace util\unittest;

use lang\{Error, IllegalArgumentException};
use unittest\actions\RuntimeVersion;
use unittest\{Assert, Expect, Test};
use util\{CompositeProperties, Properties};

class CompositePropertiesTest {

  #[Test]
  public function createCompositeSingle() {
    $c= new CompositeProperties([new Properties('')]);
    Assert::equals(1, $c->length());
  }

  #[Test]
  public function createCompositeDual() {
    $c= new CompositeProperties([new Properties('a.ini'), new Properties('b.ini')]);
    Assert::equals(2, $c->length());
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
    Assert::equals(1, $c->length());

    $c->add(new Properties('a.ini'));
    Assert::equals(2, $c->length());
  }

  #[Test]
  public function addingIdenticalPropertiesIsIdempotent() {
    $p= new Properties('');
    $c= new CompositeProperties([$p]);
    Assert::equals(1, $c->length());

    $c->add($p);
    Assert::equals(1, $c->length());
  }

  #[Test]
  public function addingEqualPropertiesIsIdempotent() {
    $c= new CompositeProperties([(new Properties())->load("[section]\na=b\nb=c")]);
    Assert::equals(1, $c->length());

    $c->add((new Properties())->load("[section]\na=b\nb=c"));
    Assert::equals(1, $c->length());
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
    Assert::equals('string...', $this->fixture()->readString('section', 'str'));
  }

  #[Test]
  public function readStringUsesSecondPropertiesWhenFirstEmpty() {
    Assert::equals('Another thing', $this->fixture()->readString('section', 'str2'));
  }

  #[Test]
  public function readStringReturnsDefaultOnNoOccurrance() {
    Assert::equals('Hello World', $this->fixture()->readString('section', 'non-existant-key', 'Hello World'));
  }

  #[Test]
  public function readStringDefaultForDefault() {
    Assert::equals('', $this->fixture()->readString('section', 'non-existant-key'));
  }

  #[Test]
  public function readBooleanUsesFirst() {
    Assert::equals(true, $this->fixture()->readBool('section', 'b1'));
  }

  #[Test]
  public function readBooleanUsesSecondIfFirstUnset() {
    Assert::equals(false, $this->fixture()->readBool('section', 'b2'));
  }

  #[Test]
  public function readBooleanUsesDefaultOnNoOccurrance() {
    Assert::equals(true, $this->fixture()->readBool('section', 'non-existant-key', true));
  }

  #[Test]
  public function readBooleanUsesFalseForDefaultOnNoOccurrance() {
    Assert::equals(false, $this->fixture()->readBool('section', 'b3'));
  }

  #[Test]
  public function readArrayUsesFirst() {
    Assert::equals(['foo', 'bar'], $this->fixture()->readArray('section', 'arr1'));
  }

  #[Test]
  public function readArrayUsesSecondIfFirstUnset() {
    Assert::equals(['foo', 'bar', 'baz'], $this->fixture()->readArray('section', 'arr2'));
  }

  #[Test]
  public function readArrayUsesDefaultOnNoOccurrance() {
    Assert::equals([1, 2, 3], $this->fixture()->readArray('section', 'non-existant-key', [1, 2, 3]));
  }

  #[Test]
  public function readArrayUsesEmptyArrayDefaultOnNoOccurrance() {
    Assert::equals([], $this->fixture()->readArray('section', 'non-existant-key'));
  }

  #[Test]
  public function readArrayDoesNotAddArrayElements() {
    Assert::equals(['foo'], $this->fixture()->readArray('section', 'arr3'));
  }

  #[Test]
  public function readMapUsesFirst() {
    Assert::equals(['a' => 'b', 'b' => 'c'], $this->fixture()->readMap('section', 'hash1'));
  }

  #[Test]
  public function readMapUsesSecondIfFirstUnset() {
    Assert::equals(['b' => 'null'], $this->fixture()->readMap('section', 'hash2'));
  }

  #[Test]
  public function readMapUsesDefaultOnNoOccurrance() {
    Assert::equals('Hello.', $this->fixture()->readMap('section', 'hash3', 'Hello.'));
  }

  #[Test]
  public function readMapUsesNullForDefaultOnNoOccurrance() {
    Assert::equals(null, $this->fixture()->readMap('section', 'hash3'));
  }

  #[Test]
  public function readIntegerUsesFirst() {
    Assert::equals(5, $this->fixture()->readInteger('section', 'int1'));
  }

  #[Test]
  public function readIntegerUsesSecondIfFirstUnset() {
    Assert::equals(4, $this->fixture()->readInteger('section', 'int2'));
  }

  #[Test]
  public function readIntegerUsesDefaultOnNoOccurrance() {
    Assert::equals(-1, $this->fixture()->readInteger('section', 'non-existant-key', -1));
  }

  #[Test]
  public function readIntegerUsesZeroForDefaultOnNoOccurrance() {
    Assert::equals(0, $this->fixture()->readInteger('section', 'non-existant-key'));
  }

  #[Test]
  public function readFloatUsesFirst() {
    Assert::equals(0.5, $this->fixture()->readFloat('section', 'float1'));
  }

  #[Test]
  public function readFloatUsesSecondIfFirstUnset() {
    Assert::equals(4.99999999, $this->fixture()->readFloat('section', 'float2'));
  }

  #[Test]
  public function readFloatUsesDefaultOnNoOccurrance() {
    Assert::equals(-1.0, $this->fixture()->readFloat('section', 'non-existant-key', -1.0));
  }

  #[Test]
  public function readFloatUsesZeroDefaultOnNoOccurrance() {
    Assert::equals(0.0, $this->fixture()->readFloat('section', 'non-existant-key'));
  }

  #[Test]
  public function readRangeUsesFirst() {
    Assert::equals([1, 2, 3], $this->fixture()->readRange('section', 'range1'));
  }

  #[Test]
  public function readRangeUsesSecondIfFirstUnset() {
    Assert::equals([99, 100], $this->fixture()->readRange('section', 'range2'));
  }

  #[Test]
  public function readRangeUsesDefaultOnNoOccurrance() {
    Assert::equals([1, 2, 3], $this->fixture()->readRange('section', 'non-existant-key', [1, 2, 3]));
  }

  #[Test]
  public function readRangeUsesEmptyArrayForDefaultOnNoOccurrance() {
    Assert::equals([], $this->fixture()->readRange('section', 'non-existant-key'));
  }

  #[Test]
  public function readSection() {
    Assert::equals(
      ['key' => 'value', 'anotherkey' => 'is there, too'],
      $this->fixture()->readSection('read')
    );
  }

  #[Test]
  public function readSectionThatDoesNotExistReturnsDefault() {
    Assert::equals(['default' => 'value'], $this->fixture()->readSection('doesnotexist', ['default' => 'value']));
  }

  #[Test]
  public function readSectionThatDoesNotExistReturnsEmptyArrayPerDefault() {
    Assert::equals([], $this->fixture()->readSection('doesnotexist'));
  }

  #[Test]
  public function readEmptySectionOverridesDefault() {
    Assert::equals([], $this->fixture()->readSection('empty', ['default' => 'value']));
  }

  #[Test]
  public function sectionFromMultipleSourcesExists() {
    Assert::equals(true, $this->fixture()->hasSection('section'));
  }

  #[Test]
  public function sectionFromSingleSourceExists() {
    Assert::equals(true, $this->fixture()->hasSection('secondsection'));
  }

  #[Test]
  public function nonexistantSectionDoesNotExist() {
    Assert::equals(false, $this->fixture()->hasSection('any'));
  }
}