<?php namespace net\xp_framework\unittest\util;
 
use unittest\TestCase;
use util\Filters;
use util\Filter;

/**
 * Test Filters class
 */
class FiltersTest extends TestCase {

  /**
   * Helper method
   *
   * @param  T[] $input
   * @param  util.Filter<T>[] $filter
   * @param  T[]
   */
  protected function filter($input, Filter $filter) {
    $output= [];
    foreach ($input as $value) {
      if ($filter->accept($value)) $output[]= $value;
    }
    return $output;
  }

  /** @return var[][] */
  protected function accepting() {
    return [[Filters::$ALL], [Filters::$ANY], [Filters::$NONE]];
  }

  #[@test, @values('accepting')]
  public function can_create($accepting) {
    create('new util.Filters<int>',
      [newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }])],
      $accepting
    );
  }

  #[@test, @values('accepting')]
  public function can_create_with_empty_filters($accepting) {
    create('new util.Filters<int>', [], $accepting);
  }

  #[@test]
  public function add_filter() {
    $filters= create('new util.Filters<int>', [], Filters::$ALL);
    $filters->add(newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }]));
    $this->assertTrue($filters->accept(2));
  }

  #[@test]
  public function allOf() {
    $this->assertEquals([2, 3], $this->filter([1, 2, 3, 4], Filters::allOf([
      newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }]),
      newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e < 4; }])
    ])));
  }

  #[@test]
  public function anyOf() {
    $this->assertEquals(['Hello', 'World', '!'], $this->filter(['Hello', 'test', '', 'World', '!'], Filters::anyOf([
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return 1 === strlen($e); }]),
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return strlen($e) > 0 && ord($e{0}) < 97; }])
    ])));
  }

  #[@test]
  public function noneOf() {
    $this->assertEquals(['index.html'], $this->filter(['file.txt', 'index.html', 'test.php'], Filters::noneOf([
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return strstr($e, 'test'); }]),
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return strstr($e, '.txt'); }])
    ])));
  }
}
