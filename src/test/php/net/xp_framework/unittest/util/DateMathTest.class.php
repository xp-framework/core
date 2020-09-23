<?php namespace net\xp_framework\unittest\util;

use unittest\Test;
use util\{Date, DateMath, TimeInterval};

class DateMathTest extends \unittest\TestCase {

  #[Test]
  public function diffSimple() {
    $this->assertEquals(
      0,
      DateMath::diff(TimeInterval::$DAY, new Date('2007-08-24'), new Date('2007-08-24'))
    );
  }
  
  #[Test]
  public function diffYesterday() {
    $this->assertEquals(
      -1,
      DateMath::diff(TimeInterval::$DAY, new Date('2007-08-24'), new Date('2007-08-23'))
    );
  }
  
  #[Test]
  public function diffTomorrow() {
    $this->assertEquals(
      1,
      DateMath::diff(TimeInterval::$DAY, new Date('2007-08-23'), new Date('2007-08-24'))
    );
  }
  
  #[Test]
  public function diffMidnightToMidnight() {
    $this->assertEquals(
      0,
      DateMath::diff(TimeInterval::$DAY, new Date('2007-08-24 00:00:00'), new Date('2007-08-24 23:59:59'))
    );
  }
  
  #[Test]
  public function diffOneSecond() {
    $this->assertEquals(
      1,
      DateMath::diff(TimeInterval::$DAY, new Date('2007-08-23 23:59:59'), new Date('2007-08-24 00:00:00'))
    );
  }
  
  #[Test]
  public function diffleapYear() {
    $this->assertEquals(
      2,
      DateMath::diff(TimeInterval::$DAY, new Date('2004-02-28 23:59:59'), new Date('2004-03-01 00:00:00'))
    );
  }

  #[Test]
  public function diffWithTimezoneOffsets() {
    $this->assertEquals(
      0,
      DateMath::diff(TimeInterval::$DAY, new Date('2000-01-01 00:00:00+0000'), new Date('2000-01-01 00:00:00+0000'))
    );
  }
  
  #[Test]
  public function diffTimezoneIndependence() {
    $this->assertEquals(
      0,
      DateMath::diff(TimeInterval::$DAY, new Date('2000-01-01 00:00:00 Europe/Berlin'), new Date('1999-12-31 23:59:59 Europe/London'))
    );
  }
  
  #[Test]
  public function diffDayInForeignTimezone() {
    $this->assertEquals(
      1,
      DateMath::diff(TimeInterval::$DAY, new Date('2007-08-27 23:59:59 Australia/Sydney'), new Date('2007-08-28 00:00:00 Australia/Sydney'))
    );
  }

  #[Test]
  public function diffMonthInForeignTimezone() {
    $this->assertEquals(
      1,
      DateMath::diff(TimeInterval::$MONTH, new Date('2008-11-30 23:59:59 Australia/Sydney'), new Date('2008-12-01 00:00:00 Australia/Sydney'))
    );
  }

  #[Test]
  public function diffYearInForeignTimezone() {
    $this->assertEquals(
      1,
      DateMath::diff(TimeInterval::$YEAR, new Date('2008-12-31 23:59:59 Australia/Sydney'), new Date('2009-01-01 00:00:00 Australia/Sydney'))
    );
  }
  
  #[Test]
  public function diffOneYear() {
    $this->assertEquals(
      365,
      DateMath::diff(TimeInterval::$DAY, new Date('2006-08-24'), new Date('2007-08-24'))
    );
  }
  
  #[Test]
  public function diffOneLeapYear() {
    $this->assertEquals(
      366,
      DateMath::diff(TimeInterval::$DAY, new Date('2004-02-24'), new Date('2005-02-24'))
    );
  }
  
  #[Test]
  public function yearDiff() {
    $this->assertEquals(0, DateMath::diff(TimeInterval::$YEAR, new Date('2007-01-01'), new Date('2007-12-31')));
    $this->assertEquals(1, DateMath::diff(TimeInterval::$YEAR, new Date('2007-01-01'), new Date('2008-01-01')));
    $this->assertEquals(-1, DateMath::diff(TimeInterval::$YEAR, new Date('2007-01-01'), new Date('2006-12-31')));
  }

  #[Test]
  public function monthDiff() {
    $this->assertEquals(0, DateMath::diff(TimeInterval::$MONTH, new Date('2004-01-01'), new Date('2004-01-31')));
    $this->assertEquals(1, DateMath::diff(TimeInterval::$MONTH, new Date('2004-02-29'), new Date('2004-03-01')));
    $this->assertEquals(0, DateMath::diff(TimeInterval::$MONTH, new Date('2005-02-29'), new Date('2005-03-01')));
    $this->assertEquals(-1, DateMath::diff(TimeInterval::$MONTH, new Date('2007-01-01'), new Date('2006-12-31')));
  }
  
  #[Test]
  public function hourDiff() {
    $this->assertEquals(0, DateMath::diff(TimeInterval::$HOURS, new Date('2007-08-12 12:00:00'), new Date('2007-08-12 12:59:59')));
    $this->assertEquals(1, DateMath::diff(TimeInterval::$HOURS, new Date('2007-08-12 12:00:00'), new Date('2007-08-12 13:00:00')));
    $this->assertEquals(-1, DateMath::diff(TimeInterval::$HOURS, new Date('2007-08-12 12:00:00'), new Date('2007-08-12 11:59:59')));
  }
}