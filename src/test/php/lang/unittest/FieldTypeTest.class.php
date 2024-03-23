<?php namespace lang\unittest;

use lang\{ArrayType, MapType, Primitive, Type, Value, XPClass};
use test\{Action, Assert, Test, Values};

class FieldTypeTest extends FieldsTest {

  /** @return iterable */
  private function types() {
    yield ['void', Type::$VOID];
    yield ['var', Type::$VAR];
    yield ['bool', Primitive::$BOOL];
    yield ['string[]', new ArrayType(Primitive::$STRING)];
    yield ['[:int]', new MapType(Primitive::$INT)];
    yield ['lang.Value', new XPClass(Value::class)];
    yield ['\\lang\\Value', new XPClass(Value::class)];
  }

  #[Test]
  public function untyped() {
    Assert::equals(Type::$VAR, $this->field('public $fixture;')->getType());
  }

  #[Test, Values(from: 'types')]
  public function field_type_determined_via_var_tag($declaration, $type) {
    Assert::equals(
      $type,
      $this->field('/** @var '.$declaration.' */ public $fixture;')->getType()
    );
  }

  #[Test, Values(from: 'types')]
  public function field_typeName_determined_via_var_tag($declaration, $type) {
    Assert::equals(
      $type->getName(),
      $this->field('/** @var '.$declaration.' */ public $fixture;')->getTypeName()
    );
  }

  #[Test, Values(from: 'types')]
  public function field_type_determined_via_type_tag($declaration, $type) {
    Assert::equals(
      $type,
      $this->field('/** @type '.$declaration.' */ public $fixture;')->getType()
    );
  }

  #[Test, Values(from: 'types')]
  public function field_type_determined_via_annotation($declaration, $type) {
    Assert::equals(
      $type,
      $this->field('#[Type("'.$declaration.'")]'."\n".'public $fixture;')->getType()
    );
  }

  #[Test]
  public function self_type_via_apidoc() {
    $fixture= $this->type('{ /** @type self */ public $fixture; }');
    Assert::equals('self', $fixture->getField('fixture')->getTypeName());
    Assert::equals($fixture, $fixture->getField('fixture')->getType());
  }

  #[Test]
  public function self_type_via_syntax() {
    $fixture= $this->type('{ public self $fixture; }');
    Assert::equals('self', $fixture->getField('fixture')->getTypeName());
    Assert::equals($fixture, $fixture->getField('fixture')->getType());
  }

  #[Test]
  public function array_of_self_type() {
    $fixture= $this->type('{ /** @type array<self> */ public $fixture; }');
    Assert::equals(new ArrayType($fixture), $fixture->getField('fixture')->getType());
  }

  #[Test]
  public function specific_array_type_determined_via_apidoc() {
    $fixture= $this->type('{ /** @type string[] */ public array $fixture; }');
    Assert::equals('string[]', $fixture->getField('fixture')->getTypeName());
    Assert::equals(new ArrayType(Primitive::$STRING), $fixture->getField('fixture')->getType());
  }

  #[Test]
  public function untyped_restriction() {
    Assert::null($this->field('public $fixture;')->getTypeRestriction());
  }

  #[Test]
  public function typed_restriction() {
    Assert::equals(Primitive::$STRING, $this->field('public string $fixture;')->getTypeRestriction());
  }
}