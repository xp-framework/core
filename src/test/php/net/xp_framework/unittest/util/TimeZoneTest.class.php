<?php namespace net\xp_framework\unittest\util;

use lang\IllegalArgumentException;
use unittest\{Expect, Test, TestCase, Values};
use util\{Date, TimeZone};

class TimeZoneTest extends TestCase {

  #[Test]
  public function name() {
    $this->assertEquals('Europe/Berlin', (new TimeZone('Europe/Berlin'))->name());
  }

  #[Test, Values([['Europe/Berlin', '2007-08-21', 7200], ['Australia/Adelaide', '2007-01-21', 37800], ['America/New_York', '2007-08-21', -14400]])]
  public function offset_daylight($tz, $date, $offset) {
    $this->assertEquals($offset, (new TimeZone($tz))->offset(new Date($date)));
  }

  #[Test, Values([['Europe/Berlin', '2007-01-21', 3600], ['Australia/Adelaide', '2007-08-21', 34200], ['America/New_York', '2007-01-21', -18000]])]
  public function offset_standard($tz, $date, $offset) {
    $this->assertEquals($offset, (new TimeZone($tz))->offset(new Date($date)));
  }

  #[Test, Values([['Europe/Berlin', '2007-08-21', '+0200'], ['Australia/Adelaide', '2007-01-21', '+1030'], ['America/New_York', '2007-08-21', '-0400']])]
  public function difference_daylight($tz, $date, $diff) {
    $this->assertEquals($diff, (new TimeZone($tz))->difference(new Date($date)));
  }

  #[Test, Values([['Europe/Berlin', '2007-01-21', '+0100'], ['Australia/Adelaide', '2007-08-21', '+0930'], ['America/New_York', '2007-01-21', '-0500']])]
  public function difference_standard($tz, $date, $diff) {
    $this->assertEquals($diff, (new TimeZone($tz))->difference(new Date($date)));
  }

  #[Test]
  public function translate() {
    $this->assertEquals(
      new Date('2006-12-31 14:00:00 Europe/Berlin'),
      (new TimeZone('Europe/Berlin'))->translate(new Date('2007-01-01 00:00 Australia/Sydney'))
    );
  }
  
  #[Test]
  public function previousTransition() {
    $transition= (new TimeZone('Europe/Berlin'))->previousTransition(new Date('2007-08-23'));
    $this->assertEquals(true, $transition->isDst());
    $this->assertEquals('CEST', $transition->abbr());
    $this->assertEquals('+0200', $transition->difference());
    $this->assertEquals(new Date('2007-03-25 02:00:00 Europe/Berlin'), $transition->date());
  }
  
  #[Test]
  public function previousPreviousTransition() {
    $transition= (new TimeZone('Europe/Berlin'))->previousTransition(new Date('2007-08-23'));
    $previous= $transition->previous();
    $this->assertFalse($previous->isDst());
    $this->assertEquals('CET', $previous->abbr());
    $this->assertEquals('+0100', $previous->difference());
    $this->assertEquals(new Date('2006-10-29 02:00:00 Europe/Berlin'), $previous->date());
  }

  #[Test]
  public function previousNextTransition() {
    $transition= (new TimeZone('Europe/Berlin'))->previousTransition(new Date('2007-08-23'));
    $next= $transition->next();
    $this->assertFalse($next->isDst());
    $this->assertEquals('CET', $next->abbr());
    $this->assertEquals('+0100', $next->difference());
    $this->assertEquals(new Date('2007-10-28 02:00:00 Europe/Berlin'), $next->date());
  }

  #[Test]
  public function nextTransition() {
    $transition= (new TimeZone('Europe/Berlin'))->nextTransition(new Date('2007-08-23'));
    $this->assertEquals(false, $transition->isDst());
    $this->assertEquals('CET', $transition->abbr());
    $this->assertEquals('+0100', $transition->difference());
    $this->assertEquals(new Date('2007-10-28 02:00:00 Europe/Berlin'), $transition->date());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unknownTimeZone() {
    new TimeZone('UNKNOWN');
  }

  /** @deprecated */
  #[Test]
  public function name_getter() {
    $this->assertEquals('Europe/Berlin', (new TimeZone('Europe/Berlin'))->getName());
  }

  /** @deprecated */
  #[Test]
  public function offsetDST() {
    $this->assertEquals('+0200', (new TimeZone('Europe/Berlin'))->getOffset(new Date('2007-08-21')));
  }

  /** @deprecated */
  #[Test]
  public function offsetNoDST() {
    $this->assertEquals('+0100', (new TimeZone('Europe/Berlin'))->getOffset(new Date('2007-01-21')));
  }

  /** @deprecated */
  #[Test]
  public function offsetWithHalfHourDST() {
    // Australia/Adelaide is +10:30 in DST
    $this->assertEquals('+1030', (new TimeZone('Australia/Adelaide'))->getOffset(new Date('2007-01-21')));
  }

  /** @deprecated */
  #[Test]
  public function offsetWithHalfHourNoDST() {
    // Australia/Adelaide is +09:30 in non-DST
    $this->assertEquals('+0930', (new TimeZone('Australia/Adelaide'))->getOffset(new Date('2007-08-21')));
  }

  /** @deprecated */
  #[Test]
  public function offsetInSecondsDST() {
    $this->assertEquals(7200, (new TimeZone('Europe/Berlin'))->getOffsetInSeconds(new Date('2007-08-21')));
  }

  /** @deprecated */
  #[Test]
  public function offsetInSecondsNoDST() {
    $this->assertEquals(3600, (new TimeZone('Europe/Berlin'))->getOffsetInSeconds(new Date('2007-01-21')));
  }
}