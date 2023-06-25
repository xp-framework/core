<?php namespace util\unittest;

use lang\{ClassLoader, Value};
use test\{Assert, Test, Values};
use util\Comparison;

class ComparisonTest {

  /** Creates a new fixture */
  private function newFixture(array $members, string $name= null): Value {
    static $uniq= 0;

    $t= ClassLoader::defineType(
      'Fixture'.($name ?? $uniq++),
      ['kind' => 'class', 'extends' => null, 'implements' => [Value::class], 'use' => [Comparison::class]],
      array_merge(['toString' => function() { }], $members)
    );
    return $t->newInstance();
  }

  #[Test]
  public function hashCode_without_members() {
    Assert::equals('FixturehashCode_without_members', $this->newFixture([], __FUNCTION__)->hashCode());
  }

  #[Test]
  public function hashCode_with_members() {
    $members= ['id' => 1, 'name' => 'Test'];
    Assert::equals(
      'FixturehashCode_with_members|i:1;|s:4:"Test";',
      $this->newFixture($members, __FUNCTION__)->hashCode()
    );
  }

  #[Test]
  public function compareTo_self() {
    $fixture= $this->newFixture([]);
    Assert::equals(0, $fixture->compareTo($fixture));
  }

  #[Test]
  public function compareTo_instance_without_members() {
    $a= $this->newFixture([], __FUNCTION__);
    $b= $this->newFixture([], __FUNCTION__);
    Assert::equals(0, $a->compareTo($b));
  }

  #[Test]
  public function compareTo_instance_with_members() {
    $a= $this->newFixture(['id' => 1], __FUNCTION__);
    $b= $this->newFixture(['id' => 1], __FUNCTION__);
    Assert::equals(0, $a->compareTo($b));
  }

  #[Test]
  public function compareTo_cloned_instance_with_different_member() {
    $a= $this->newFixture(['id' => 1]);
    $b= clone $a;
    $b->id++;
    Assert::equals(-1, $a->compareTo($b));
  }

  #[Test, Values([[1], [1.0], [true], ['string'], [null], [[]]])]
  public function compareTo_any_other($value) {
    Assert::equals(1, $this->newFixture([])->compareTo($this));
  }

  #[Test]
  public function compare_objects_with_private_members() {
    $object= new class() implements Value {
      use Comparison;
      private $member= 1;
      public function toString() { return nameof($this).'<'.$this->member.'>'; }
    };
    Assert::equals(0, $object->compareTo($object));
  }
}