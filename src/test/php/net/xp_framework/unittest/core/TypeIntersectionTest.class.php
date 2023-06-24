<?php namespace net\xp_framework\unittest\core;

use lang\{ClassCastException, IllegalArgumentException, Type, TypeIntersection, XPClass};
use net\xp_framework\unittest\Name;
use unittest\Assert;
use unittest\actions\RuntimeVersion;
use unittest\{Action, Expect, Test, TestCase, Values};

class TypeIntersectionTest {
  private $types;

  /** @return void */
  #[Before]
  public function setUp() {
    $this->types= [new XPClass('Countable'), new XPClass('Traversable')];
  }

  /** @return Countable&Traversable */
  private function intersection() {
    return new class() implements \Countable, \IteratorAggregate {
      public function count(): int { return 0; }
      public function getIterator(): \Traversable { while (false) yield; }
    };
  }

  /** @return Countable */
  private function countable() {
    return new class() implements \Countable {
      public function count(): int { return 0; }
    };
  }

  /** @return Traversable */
  private function traversable() {
    return new class() implements \IteratorAggregate {
      public function getIterator(): \Traversable { while (false) yield; }
    };
  }

  /** @return iterable */
  private function values() {
    yield [0];
    yield [false];
    yield [''];
    yield [[]];
    yield [$this->countable()];
    yield [$this->traversable()];
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_create_from_empty() {
    new TypeIntersection([]);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function cannot_create_from_single() {
    new TypeIntersection([Type::$VAR]);
  }

  #[Test]
  public function can_create() {
    new TypeIntersection($this->types);
  }

  #[Test, Values(['Countable&Traversable', 'Countable & Traversable', 'Traversable&Countable'])]
  public function forName($literal) {
    Assert::equals(new TypeIntersection($this->types), TypeIntersection::forName($literal));
  }

  #[Test, Values(['Countable&Traversable', 'Countable & Traversable', '(Countable&Traversable)', '(Countable & Traversable)'])]
  public function forName_from_Type_class($literal) {
    Assert::equals(new TypeIntersection($this->types), Type::forName($literal));
  }

  #[Test]
  public function types() {
    Assert::equals($this->types, (new TypeIntersection($this->types))->types());
  }

  #[Test]
  public function is_instance() {
    Assert::true((new TypeIntersection($this->types))->isInstance($this->intersection()));
  }

  #[Test, Values('values')]
  public function is_not_instance($value) {
    Assert::false((new TypeIntersection($this->types))->isInstance($value));
  }

  #[Test]
  public function new_instance() {
    $i= $this->intersection();
    Assert::equals($i, (new TypeIntersection($this->types))->newInstance($i));
  }

  #[Test]
  public function cast() {
    $i= $this->intersection();
    Assert::equals($i, (new TypeIntersection($this->types))->cast($i));
  }

  #[Test]
  public function cast_null() {
    Assert::null((new TypeIntersection($this->types))->cast(null));
  }

  #[Test, Expect(ClassCastException::class), Values('values')]
  public function cannot_cast($value) {
    (new TypeIntersection($this->types))->cast($value);
  }

  #[Test, Values(['Traversable&Countable', 'Countable&Traversable', 'Countable&IteratorAggregate', 'Countable&Traversable&ArrayAccess'])]
  public function is_assignable_from_intersection($type) {
    Assert::true(TypeIntersection::forName($type)->isAssignableFrom(new TypeIntersection($this->types)));
  }

  #[Test, Values(['ArrayObject', 'SplFixedArray'])]
  public function is_assignable_from_class($type) {
    Assert::true((new TypeIntersection($this->types))->isAssignableFrom($type));
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1.0-dev")')]
  public function php81_native_intersection_field_type() {
    $f= typeof(eval('return new class() { public Countable&Traversable $fixture; };'))->getField('fixture');
    Assert::equals(new TypeIntersection($this->types), $f->getType());
    Assert::equals('Countable&Traversable', $f->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1.0-dev")')]
  public function php81_native_intersection_param_type() {
    $m= typeof(eval('return new class() { public function fixture(Countable&Traversable $arg) { } };'))->getMethod('fixture');
    Assert::equals(new TypeIntersection($this->types), $m->getParameter(0)->getType());
    Assert::equals('Countable&Traversable', $m->getParameter(0)->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1.0-dev")')]
  public function php81_native_intersection_return_type() {
    $m= typeof(eval('return new class() { public function fixture(): Countable&Traversable { } };'))->getMethod('fixture');
    Assert::equals(new TypeIntersection($this->types), $m->getReturnType());
    Assert::equals('Countable&Traversable', $m->getReturnTypeName());
  }
}