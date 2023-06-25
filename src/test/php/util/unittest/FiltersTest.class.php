<?php namespace util\unittest;

use lang\{IllegalArgumentException, IllegalStateException};
use test\{Assert, Expect, Test, Values};
use util\{Filter, Filters};

class FiltersTest {

  /**
   * Helper method
   *
   * @param  var[] $input
   * @param  util.Filter<var>[] $filter
   * @param  php.Iterator
   */
  protected function filter($input, Filter $filter) {
    foreach ($input as $value) {
      if ($filter->accept($value)) yield $value;
    }
  }

  /** @return var[][] */
  protected function accepting() {
    return [[Filters::$ALL], [Filters::$ANY], [Filters::$NONE]];
  }

  #[Test, Values(from: 'accepting')]
  public function can_create($accepting) {
    create('new util.Filters<int>',
      [newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }])],
      $accepting
    );
  }

  #[Test, Values(from: 'accepting')]
  public function can_create_with_empty_filters($accepting) {
    create('new util.Filters<int>', [], $accepting);
  }

  #[Test]
  public function can_create_with_empty_acceptor() {
    create('new util.Filters<int>',
      [newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }])],
      null
    );
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function constructor_raises_exception_when_neither_null_nor_closure_given_for_accepting() {
    create('new util.Filters<int>', [], 'callback');
  }

  #[Test]
  public function add_filter() {
    $filters= create('new util.Filters<int>', [], Filters::$ALL);
    $filters->add(newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }]));
    Assert::true($filters->accept(2));
  }

  #[Test]
  public function set_accepting() {
    $filters= create('new util.Filters<int>', [newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }])]);
    $filters->accepting(Filters::$ALL);
    Assert::true($filters->accept(2));
  }

  #[Test]
  public function fluent_interface() {
    Assert::true(create('new util.Filters<int>')
      ->add(newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }]))
      ->accepting(Filters::$ALL)
      ->accept(2)
    );
  }

  #[Test, Expect(IllegalStateException::class)]
  public function accept_called_without_accepting_function_set() {
    create('new util.Filters<int>')
      ->add(newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }]))
      ->accept(2)
    ;
  }

  #[Test]
  public function allOf() {
    Assert::equals([2, 3], iterator_to_array($this->filter([1, 2, 3, 4], Filters::allOf([
      newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e > 1; }]),
      newinstance('util.Filter<int>', [], ['accept' => function($e) { return $e < 4; }])
    ]))));
  }

  #[Test]
  public function anyOf() {
    Assert::equals(['Hello', 'World', '!'], iterator_to_array($this->filter(['Hello', 'test', '', 'World', '!'], Filters::anyOf([
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return 1 === strlen($e); }]),
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return strlen($e) > 0 && ord($e[0]) < 97; }])
    ]))));
  }

  #[Test]
  public function noneOf() {
    Assert::equals(['index.html'], iterator_to_array($this->filter(['file.txt', 'index.html', 'test.php'], Filters::noneOf([
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return strstr($e, 'test'); }]),
      newinstance('util.Filter<string>', [], ['accept' => function($e) { return strstr($e, '.txt'); }])
    ]))));
  }
}