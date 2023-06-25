<?php namespace lang\unittest;

use lang\{IllegalAccessException, Primitive, XPClass};
use unittest\{Assert, Before, Expect, Test};

class VirtualMembersTest {
  private $property;

  #[Before]
  public function fixtures() {
    $this->property= XPClass::forName('lang.unittest.WithReadonly');
  }

  #[Test]
  public function exists() {
    Assert::true($this->property->hasField('prop'));
  }

  #[Test]
  public function string_representation() {
    Assert::equals(
      'public readonly string '.$this->property->getName().'::$prop',
      $this->property->getField('prop')->toString()
    );
  }

  #[Test]
  public function type() {
    Assert::equals(Primitive::$STRING, $this->property->getField('prop')->getType());
  }

  #[Test]
  public function type_name() {
    Assert::equals('string', $this->property->getField('prop')->getTypeName());
  }

  #[Test]
  public function type_restriction() {
    Assert::equals(Primitive::$STRING, $this->property->getField('prop')->getTypeRestriction());
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function cannot_access_by_default() {
    $this->property->getField('prop')->get($this->property->newInstance());
  }

  #[Test]
  public function initial_value() {
    Assert::null($this->property->getField('prop')->setAccessible(true)->get($this->property->newInstance()));
  }

  #[Test]
  public function get_set_roundtrip() {
    with ($this->property->getField('prop')->setAccessible(true), $this->property->newInstance(), function($field, $instance) {
      $field->set($instance, [$this]);
      Assert::equals([$this], $field->get($instance));
    });
  }

  #[Test]
  public function included_in_all_fields() {
    Assert::equals(
      ['__readonly', 'prop'],
      array_map(function($f) { return $f->getName(); }, $this->property->getFields())
    );
  }

  #[Test]
  public function included_in_all_declared_fields() {
    Assert::equals(
      ['__readonly', 'prop'],
      array_map(function($f) { return $f->getName(); }, $this->property->getDeclaredFields())
    );
  }
}