<?php namespace net\xp_framework\unittest\core;

use lang\{Type, TypeIntersection, XPClass, IllegalArgumentException, ClassCastException};
use net\xp_framework\unittest\Name;
use unittest\actions\RuntimeVersion;
use unittest\{Action, Expect, Test, TestCase, Values};

class TypeIntersectionTest extends TestCase {
  private $types;

  /** @return void */
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
    $this->assertEquals(new TypeIntersection($this->types), TypeIntersection::forName($literal));
  }

  #[Test, Values(['Countable&Traversable', 'Countable & Traversable', '(Countable&Traversable)', '(Countable & Traversable)'])]
  public function forName_from_Type_class($literal) {
    $this->assertEquals(new TypeIntersection($this->types), Type::forName($literal));
  }

  #[Test]
  public function types() {
    $this->assertEquals($this->types, (new TypeIntersection($this->types))->types());
  }

  #[Test]
  public function is_instance() {
    $this->assertTrue((new TypeIntersection($this->types))->isInstance($this->intersection()));
  }

  #[Test, Values('values')]
  public function is_not_instance($value) {
    $this->assertFalse((new TypeIntersection($this->types))->isInstance($value));
  }

  #[Test]
  public function new_instance() {
    $i= $this->intersection();
    $this->assertEquals($i, (new TypeIntersection($this->types))->newInstance($i));
  }

  #[Test]
  public function cast() {
    $i= $this->intersection();
    $this->assertEquals($i, (new TypeIntersection($this->types))->cast($i));
  }

  #[Test]
  public function cast_null() {
    $this->assertNull((new TypeIntersection($this->types))->cast(null));
  }

  #[Test, Expect(ClassCastException::class), Values('values')]
  public function cannot_cast($value) {
    (new TypeIntersection($this->types))->cast($value);
  }

  #[Test, Values(['Traversable&Countable', 'Countable&Traversable', 'Countable&IteratorAggregate', 'Countable&Traversable&ArrayAccess'])]
  public function is_assignable_from($type) {
    $this->assertTrue(TypeIntersection::forName($type)->isAssignableFrom(new TypeIntersection($this->types)));
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1.0-dev")')]
  public function php81_native_intersection_field_type() {
    $f= typeof(eval('return new class() { public Countable&Traversable $fixture; };'))->getField('fixture');
    $this->assertEquals(new TypeIntersection($this->types), $f->getType());
    $this->assertEquals('Countable&Traversable', $f->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1.0-dev")')]
  public function php81_native_intersection_param_type() {
    $m= typeof(eval('return new class() { public function fixture(Countable&Traversable $arg) { } };'))->getMethod('fixture');
    $this->assertEquals(new TypeIntersection($this->types), $m->getParameter(0)->getType());
    $this->assertEquals('Countable&Traversable', $m->getParameter(0)->getTypeName());
  }

  #[Test, Action(eval: 'new RuntimeVersion(">=8.1.0-dev")')]
  public function php81_native_intersection_return_type() {
    $m= typeof(eval('return new class() { public function fixture(): Countable&Traversable { } };'))->getMethod('fixture');
    $this->assertEquals(new TypeIntersection($this->types), $m->getReturnType());
    $this->assertEquals('Countable&Traversable', $m->getReturnTypeName());
  }
}