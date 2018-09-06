<?php namespace net\xp_framework\unittest\util;

use lang\IllegalArgumentException;
use util\Date;
use util\TimeZone;

class TimeZoneTest extends \unittest\TestCase {
  private $fixture;

  /** @return void */
  public function setUp() {
    $this->fixture= new TimeZone('Europe/Berlin');
  }    

  #[@test]
  public function name() {
    $this->assertEquals('Europe/Berlin', $this->fixture->getName());
  }
  
  #[@test]
  public function offsetDST() {
    $this->assertEquals('+0200', $this->fixture->getOffset(new Date('2007-08-21')));
  }
  
  #[@test]
  public function offsetNoDST() {
    $this->assertEquals('+0100', $this->fixture->getOffset(new Date('2007-01-21')));
  }
  
  #[@test]
  public function offsetWithHalfHourDST() {
    // Australia/Adelaide is +10:30 in DST
    $this->fixture= new TimeZone('Australia/Adelaide');
    $this->assertEquals('+1030', $this->fixture->getOffset(new Date('2007-01-21')));
  }
  
  #[@test]
  public function offsetWithHalfHourNoDST() {
    // Australia/Adelaide is +09:30 in non-DST
    $this->fixture= new TimeZone('Australia/Adelaide');
    $this->assertEquals('+0930', $this->fixture->getOffset(new Date('2007-08-21')));
  }
  
  #[@test]
  public function offsetInSecondsDST() {
    $this->assertEquals(7200, $this->fixture->getOffsetInSeconds(new Date('2007-08-21')));
  }
  
  #[@test]
  public function offsetInSecondsNoDST() {
    $this->assertEquals(3600, $this->fixture->getOffsetInSeconds(new Date('2007-01-21')));
  }
  
  #[@test]
  public function convert() {
    $date= new Date('2007-01-01 00:00 Australia/Sydney');
    $this->assertEquals(
      new Date('2006-12-31 14:00:00 Europe/Berlin'),
      $this->fixture->translate($date)
    );
  }
  
  #[@test]
  public function previousTransition() {
    $transition= $this->fixture->previousTransition(new Date('2007-08-23'));
    $this->assertEquals(true, $transition->isDst());
    $this->assertEquals('CEST', $transition->abbr());
    $this->assertEquals(new Date('2007-03-25 02:00:00 Europe/Berlin'), $transition->getDate());
  }
  
  #[@test]
  public function previousPreviousTransition() {
    $transition= $this->fixture->previousTransition(new Date('2007-08-23'));
    $previous= $transition->previous();
    $this->assertFalse($previous->isDst());
    $this->assertEquals('CET', $previous->abbr());
    $this->assertEquals(new Date('2006-10-29 02:00:00 Europe/Berlin'), $previous->getDate());
  }

  #[@test]
  public function nextTransition() {
    $transition= $this->fixture->nextTransition(new Date('2007-08-23'));
    $this->assertEquals(false, $transition->isDst());
    $this->assertEquals('CET', $transition->abbr());
    $this->assertEquals(new Date('2007-10-28 02:00:00 Europe/Berlin'), $transition->getDate());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function unknownTimeZone() {
    new TimeZone('UNKNOWN');
  }
}
