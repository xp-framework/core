<?php namespace net\xp_framework\unittest\reflection;

use lang\{IllegalAccessException, Primitive};
use unittest\{Assert, Before, Expect, Test};

class VirtualMembersTest {
  use TypeDefinition { type as declare; }

  private $property;

  #[Before]
  public function fixtures() {
    $this->property= $this->declare('{
      const __VIRTUAL= [["field" => [MODIFIER_PRIVATE, "string"]]]; 

      private $backing= ["field" => null];

      public function __get($name) {
        return isset(self::__VIRTUAL[0][$name]) ? $this->backing[$name] : null;
      }

      public function __set($name, $value) {
        if (isset(self::__VIRTUAL[0][$name])) {
          $this->backing[$name]= $value;
        }
      }
    }', ['use' => []]);
  }

  #[Test]
  public function exists() {
    Assert::true($this->property->hasField('field'));
  }

  #[Test]
  public function string_representation() {
    Assert::equals(
      'private string '.$this->property->getName().'::$field',
      $this->property->getField('field')->toString()
    );
  }

  #[Test]
  public function type() {
    Assert::equals(Primitive::$STRING, $this->property->getField('field')->getType());
  }

  #[Test]
  public function type_name() {
    Assert::equals('string', $this->property->getField('field')->getTypeName());
  }

  #[Test]
  public function type_restriction() {
    Assert::equals(Primitive::$STRING, $this->property->getField('field')->getTypeRestriction());
  }

  #[Test, Expect(IllegalAccessException::class)]
  public function cannot_access_by_default() {
    $this->property->getField('field')->get($this->property->newInstance());
  }

  #[Test]
  public function initial_value() {
    Assert::null($this->property->getField('field')->setAccessible(true)->get($this->property->newInstance()));
  }

  #[Test]
  public function get_set_roundtrip() {
    with ($this->property->getField('field')->setAccessible(true), $this->property->newInstance(), function($field, $instance) {
      $field->set($instance, [$this]);
      Assert::equals([$this], $field->get($instance));
    });
  }

  #[Test]
  public function included_in_all_fields() {
    Assert::equals(
      ['backing', 'field'],
      array_map(function($f) { return $f->getName(); }, $this->property->getFields())
    );
  }

  #[Test]
  public function included_in_all_declared_fields() {
    Assert::equals(
      ['backing', 'field'],
      array_map(function($f) { return $f->getName(); }, $this->property->getDeclaredFields())
    );
  }
}