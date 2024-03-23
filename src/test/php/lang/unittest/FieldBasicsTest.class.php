<?php namespace lang\unittest;

use lang\ElementNotFoundException;
use lang\reflect\Field;
use test\{Assert, Expect, Test, Values};

class FieldBasicsTest extends FieldsTest {

  #[Test]
  public function declaring_class() {
    $fixture= $this->type('{ public $declared; }');
    Assert::equals($fixture, $fixture->getField('declared')->getDeclaringClass());
  }

  #[Test]
  public function has_field_for_existant() {
    Assert::true($this->type('{ public $declared; }')->hasField('declared'));
  }

  #[Test]
  public function has_field_for_non_existant() {
    Assert::false($this->type()->hasField('@@nonexistant@@'));
  }

  #[Test]
  public function has_field_for_special() {
    Assert::false($this->type()->hasField('__id'));
  }

  #[Test]
  public function get_existant_Field() {
    Assert::instance(Field::class, $this->type('{ public $declared; }')->getField('declared'));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function get_non_existant_Field() {
    $this->type()->getField('@@nonexistant@@');
  }
  
  #[Test, Expect(ElementNotFoundException::class)]
  public function get_field_for_special() {
    $this->type()->getField('__id');
  }

  #[Test]
  public function name() {
    Assert::equals('fixture', $this->field('public $fixture;')->getName());
  }

  #[Test]
  public function equality() {
    $fixture= $this->type('{ public $fixture; }');
    Assert::equals($fixture->getField('fixture'), $fixture->getField('fixture'));
  }

  #[Test]
  public function a_field_is_not_equal_to_null() {
    Assert::notEquals($this->field('public $fixture;'), null);
  }

  #[Test, Values([['public $fixture;', 'public var %s::$fixture'], ['private $fixture;', 'private var %s::$fixture'], ['protected $fixture;', 'protected var %s::$fixture'], ['static $fixture;', 'public static var %s::$fixture'], ['private static $fixture;', 'private static var %s::$fixture'], ['protected static $fixture;', 'protected static var %s::$fixture'], ['/** @var int */ public $fixture;', 'public int %s::$fixture']])]
  public function string_representation($declaration, $expected) {
    $fixture= $this->type('{ '.$declaration.' }');
    Assert::equals(sprintf($expected, $fixture->getName()), $fixture->getField('fixture')->toString());
  }

  #[Test]
  public function trait_field_type() {
    Assert::equals('int', $this->type()->getField('NOT_INSTANCE')->getTypeName());
  }

  #[Test]
  public function all_fields() {
    $fixture= $this->type('{ public $a= "Test", $b; }', [
      'use' => []
    ]);
    Assert::equals(
      ['a', 'b'],
      array_map(fn($f) => $f->getName(), $fixture->getFields())
    );
  }

  #[Test]
  public function all_fields_include_base_class() {
    $fixture= $this->type('{ public $declared= "Test"; public function getDate() { return null; }}', [
      'use'     => [],
      'extends' => [AbstractTestClass::class]
    ]);
    Assert::equals(
      ['declared', 'inherited'],
      array_map(fn($f) => $f->getName(), $fixture->getFields())
    );
  }

  #[Test]
  public function declared_fields() {
    $fixture= $this->type('{ public $a= "Test", $b; }', [
      'use' => []
    ]);
    Assert::equals(
      ['a', 'b'],
      array_map(fn($f) => $f->getName(), $fixture->getDeclaredFields())
    );
  }
}