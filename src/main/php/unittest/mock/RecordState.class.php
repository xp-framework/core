<?php namespace unittest\mock;

use lang\IllegalArgumentException;
use util\collections\HashTable;

/**
 * Record expectations on a mock object.
 *
 * @test  xp://net.xp_framework.unittest.tests.mock.RecordStateTest
 */
class RecordState extends \lang\Object implements IMockState {
  private $expectationMap = null;

  /**
   * Constructor
   *
   * @param   util.collections.HashTable $expectationsMap
   */
  public function  __construct(HashTable $expectationMap) {
    $this->expectationMap= $expectationMap;
  }

  /**
   * Records the call as expectation and returns the mehtod options object.
   *
   * @param   string method the method name
   * @param   var[] args an array of arguments
   * @return  var
   */
  public function handleInvocation($method, $args) {
    $expectation= new Expectation($method);
    $expectation->setArguments($args);

    if ($this->expectationMap->containsKey($method)) {
      $methodExpectations= $this->expectationMap[$method];
    } else {
      $methodExpectations= new ExpectationList();
      $this->expectationMap[$method]= $methodExpectations;
    }
    $methodExpectations->add($expectation);

    return new MethodOptions($expectation, $method);
  }
}
