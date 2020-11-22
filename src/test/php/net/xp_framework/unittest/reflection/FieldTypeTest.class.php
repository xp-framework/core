<?php namespace net\xp_framework\unittest\reflection;

use lang\{ArrayType, MapType, Primitive, Type, Value, XPClass};
use unittest\actions\RuntimeVersion;
use unittest\{Action, Test, Values};

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
    $this->assertEquals(Type::$VAR, $this->field('public $fixture;')->getType());
  }

  #[Test, Values('types')]
  public function field_type_determined_via_var_tag($declaration, $type) {
    $this->assertEquals(
      $type,
      $this->field('/** @var '.$declaration.' */ public $fixture;')->getType()
    );
  }

  #[Test, Values('types')]
  public function field_typeName_determined_via_var_tag($declaration, $type) {
    $this->assertEquals(
      $type->getName(),
      $this->field('/** @var '.$declaration.' */ public $fixture;')->getTypeName()
    );
  }

  #[Test, Values('types')]
  public function field_type_determined_via_type_tag($declaration, $type) {
    $this->assertEquals(
      $type,
      $this->field('/** @type '.$declaration.' */ public $fixture;')->getType()
    );
  }

  #[Test, Values('types')]
  public function field_type_determined_via_annotation($declaration, $type) {
    $this->assertEquals(
      $type,
      $this->field('#[Type("'.$declaration.'")]'."\n".'public $fixture;')->getType()
    );
  }

  #[Test]
  public function self_type_via_apidoc() {
    $fixture= $this->type('{ /** @type self */ public $fixture; }');
    $this->assertEquals('self', $fixture->getField('fixture')->getTypeName());
    $this->assertEquals($fixture, $fixture->getField('fixture')->getType());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.4")')]
  public function self_type_via_syntax() {
    $fixture= $this->type('{ public self $fixture; }');
    $this->assertEquals('self', $fixture->getField('fixture')->getTypeName());
    $this->assertEquals($fixture, $fixture->getField('fixture')->getType());
  }

  #[Test]
  public function array_of_self_type() {
    $fixture= $this->type('{ /** @type array<self> */ public $fixture; }');
    $this->assertEquals(new ArrayType($fixture), $fixture->getField('fixture')->getType());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=7.4")')]
  public function specific_array_type_determined_via_apidoc() {
    $fixture= $this->type('{ /** @type string[] */ public array $fixture; }');
    $this->assertEquals('string[]', $fixture->getField('fixture')->getTypeName());
    $this->assertEquals(new ArrayType(Primitive::$STRING), $fixture->getField('fixture')->getType());
  }
}