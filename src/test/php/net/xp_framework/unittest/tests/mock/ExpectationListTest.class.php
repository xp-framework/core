<?php namespace net\xp_framework\unittest\tests\mock;

use unittest\mock\ExpectationList;
use unittest\mock\Expectation;

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
    $this->sut->getNext(array());
  }
  
  #[@test]
  public function getNext_returnNullByDefault() {
    $this->assertNull($this->sut->getNext(array()));
  }
  
  #[@test]
  public function canAddExpectation() {
    $this->sut->add(new Expectation('method'));
  }
  
  #[@test]
  public function getNextReturnsAddedExpectation() {
    $expect= new Expectation('method');
    $this->sut->add($expect);    
    $this->assertEquals($expect, $this->sut->getNext(array()));
  }
  
  #[@test]
  public function getNextReturns_should_return_last_expectation_over_and_over() {
    $expect= new Expectation('method');
    $this->sut->add($expect);
    $this->assertEquals($expect, $this->sut->getNext(array()));
    $this->assertEquals($expect, $this->sut->getNext(array()));
    $this->assertEquals($expect, $this->sut->getNext(array()));
  }
  
  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannotAddNull() {
    $this->sut->add(null);
  }
  
  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannotAddObjects() {
    $this->sut->add(new \lang\Object());
  }

  #[@test]
  public function getNext_SameExpectationTwice_whenRepeatIs2() {
    $expect= new Expectation('method');
    $expect->setRepeat(2);
    $this->sut->add($expect);
    $this->assertEquals($expect, $this->sut->getNext(array()));
    $this->assertEquals($expect, $this->sut->getNext(array()));
    $this->assertNull($this->sut->getNext(array()));
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
    $this->sut->getNext(array());    
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
    $this->sut->getNext(array());
    $list= $this->sut->getCalled();
    $this->assertEquals(1, $list->size());
    $this->assertEquals($expect, $list[0]);
  }
}
