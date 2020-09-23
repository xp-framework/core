<?php namespace net\xp_framework\unittest\reflection;

use lang\ElementNotFoundException;
use lang\reflect\Field;
use unittest\{Expect, Test, Values};

class FieldBasicsTest extends FieldsTest {

  #[Test]
  public function declaring_class() {
    $fixture= $this->type('{ public $declared; }');
    $this->assertEquals($fixture, $fixture->getField('declared')->getDeclaringClass());
  }

  #[Test]
  public function has_field_for_existant() {
    $this->assertTrue($this->type('{ public $declared; }')->hasField('declared'));
  }

  #[Test]
  public function has_field_for_non_existant() {
    $this->assertFalse($this->type()->hasField('@@nonexistant@@'));
  }

  #[Test]
  public function has_field_for_special() {
    $this->assertFalse($this->type()->hasField('__id'));
  }

  #[Test]
  public function get_existant_Field() {
    $this->assertInstanceOf(Field::class, $this->type('{ public $declared; }')->getField('declared'));
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
    $this->assertEquals('fixture', $this->field('public $fixture;')->getName());
  }

  #[Test]
  public function equality() {
    $fixture= $this->type('{ public $fixture; }');
    $this->assertEquals($fixture->getField('fixture'), $fixture->getField('fixture'));
  }

  #[Test]
  public function a_field_is_not_equal_to_null() {
    $this->assertNotEquals($this->field('public $fixture;'), null);
  }

  #[Test, Values([['public $fixture;', 'public var %s::$fixture'], ['private $fixture;', 'private var %s::$fixture'], ['protected $fixture;', 'protected var %s::$fixture'], ['static $fixture;', 'public static var %s::$fixture'], ['private static $fixture;', 'private static var %s::$fixture'], ['protected static $fixture;', 'protected static var %s::$fixture'], ['/** @var int */ public $fixture;', 'public int %s::$fixture']])]
  public function string_representation($declaration, $expected) {
    $fixture= $this->type('{ '.$declaration.' }');
    $this->assertEquals(sprintf($expected, $fixture->getName()), $fixture->getField('fixture')->toString());
  }

  #[Test]
  public function trait_field_type() {
    $this->assertEquals('int', $this->type()->getField('NOT_INSTANCE')->getTypeName());
  }
}