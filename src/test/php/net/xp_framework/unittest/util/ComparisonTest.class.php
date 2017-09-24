<?php namespace net\xp_framework\unittest\util;

use lang\{Value, ClassLoader};
use util\Comparison;

class ComparisonTest extends \unittest\TestCase {

  /** Creates a new fixture */
  private function newFixture(array $members): Value {
    $t= ClassLoader::defineType(
      $this->name.'Fixture',
      ['kind' => 'class', 'extends' => null, 'implements' => [Value::class], 'use' => [Comparison::class]],
      array_merge(['toString' => function() { }], $members)
    );
    return $t->newInstance();
  }

  #[@test]
  public function hashCode_without_members() {
    $this->assertEquals('hashCode_without_membersFixture', $this->newFixture([])->hashCode());
  }

  #[@test]
  public function hashCode_with_members() {
    $members= ['id' => 1, 'name' => 'Test'];
    $this->assertEquals('hashCode_with_membersFixture|i:1;|s:4:"Test";', $this->newFixture($members)->hashCode());
  }

  #[@test]
  public function compareTo_self() {
    $fixture= $this->newFixture([]);
    $this->assertEquals(0, $fixture->compareTo($fixture));
  }

  #[@test]
  public function compareTo_instance_without_members() {
    $a= $this->newFixture([]);
    $b= $this->newFixture([]);
    $this->assertEquals(0, $a->compareTo($b));
  }

  #[@test]
  public function compareTo_instance_with_members() {
    $a= $this->newFixture(['id' => 1]);
    $b= $this->newFixture(['id' => 1]);
    $this->assertEquals(0, $a->compareTo($b));
  }

  #[@test]
  public function compareTo_cloned_instance_with_different_member() {
    $a= $this->newFixture(['id' => 1]);
    $b= clone $a;
    $b->id++;
    $this->assertEquals(-1, $a->compareTo($b));
  }

  #[@test, @values([[1], [1.0], [true], ['string'], [null], [[]]])]
  public function compareTo_any_other($value) {
    $this->assertEquals(1, $this->newFixture([])->compareTo($this));
  }
}