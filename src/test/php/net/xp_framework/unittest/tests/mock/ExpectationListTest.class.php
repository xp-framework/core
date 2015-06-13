<?php namespace net\xp_framework\unittest\tests\mock;

use unittest\mock\ExpectationList;
use unittest\mock\Expectation;
use unittest\actions\RuntimeVersion;

/**
 * Test cases for the ExpectationList class
 *
 * @see   xp://unittest.mock.ExpectationList
 */
class ExpectationListTest extends \unittest\TestCase {
  private $sut= null;

  /**
   * Creates the fixture;
   */
  public function setUp() {
    $this->sut= new ExpectationList();
  }
    
  #[@test]
  public function canCreate() {
    new ExpectationList();
  }

  #[@test]
  public function canCallGetNext() {
    $this->sut->getNext([]);
  }
  
  #[@test]
  public function getNext_returnNullByDefault() {
    $this->assertNull($this->sut->getNext([]));
  }
  
  #[@test]
  public function canAddExpectation() {
    $this->sut->add(new Expectation('method'));
  }
  
  #[@test]
  public function getNextReturnsAddedExpectation() {
    $expect= new Expectation('method');
    $this->sut->add($expect);    
    $this->assertEquals($expect, $this->sut->getNext([]));
  }
  
  #[@test]
  public function getNextReturns_should_return_last_expectation_over_and_over() {
    $expect= new Expectation('method');
    $this->sut->add($expect);
    $this->assertEquals($expect, $this->sut->getNext([]));
    $this->assertEquals($expect, $this->sut->getNext([]));
    $this->assertEquals($expect, $this->sut->getNext([]));
  }
  
  #[@test, @expect('lang.IllegalArgumentException'), @action(new RuntimeVersion('<7.0.0alpha1'))]
  public function cannotAddNull() {
    $this->sut->add(null);
  }
  
  #[@test, @expect('lang.IllegalArgumentException'), @action(new RuntimeVersion('<7.0.0alpha1'))]
  public function cannotAddObjects() {
    $this->sut->add(new \lang\Object());
  }

  #[@test, @expect('lang.Error'), @action(new RuntimeVersion('>=7.0.0alpha1'))]
  public function cannotAddNull7() {
    $this->sut->add(null);
  }
  
  #[@test, @expect('lang.Error'), @action(new RuntimeVersion('>=7.0.0alpha1'))]
  public function cannotAddObjects7() {
    $this->sut->add(new \lang\Object());
  }

  #[@test]
  public function getNext_SameExpectationTwice_whenRepeatIs2() {
    $expect= new Expectation('method');
    $expect->setRepeat(2);
    $this->sut->add($expect);
    $this->assertEquals($expect, $this->sut->getNext([]));
    $this->assertEquals($expect, $this->sut->getNext([]));
    $this->assertNull($this->sut->getNext([]));
  }

  #[@test]
  public function should_provide_access_to_left_expectations() {
    $expect= new Expectation('method');
    $this->sut->add($expect);
    $list= $this->sut->getExpectations();
    $this->assertEquals(1, $list->size());
    $this->assertEquals($expect, $list[0]);
  }

  #[@test]
  public function should_provide_access_to_used_expectations() {
    $expect= new Expectation('method');
    $this->sut->add($expect);
    $this->sut->getNext([]);    
    $list= $this->sut->getCalled();
    $this->assertEquals(1, $list->size());
    $this->assertEquals($expect, $list[0]);
  }

  #[@test]
  public function expectation_should_be_moved_to_calledList_after_usage() {
    $expect= new Expectation('method');
    $this->sut->add($expect);
    $list= $this->sut->getExpectations();
    $this->assertEquals(1, $list->size());
    $this->assertEquals($expect, $list[0]);
    $this->sut->getNext([]);
    $list= $this->sut->getCalled();
    $this->assertEquals(1, $list->size());
    $this->assertEquals($expect, $list[0]);
  }
}
