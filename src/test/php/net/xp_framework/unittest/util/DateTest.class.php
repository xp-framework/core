<?php namespace net\xp_framework\unittest\util;
 
use lang\IllegalArgumentException;
use lang\IllegalStateException;
use util\Date;
use util\TimeZone;

/**
 * Tests Date class
 *
 * @see http://www.php.net/manual/en/datetime.formats.time.php
 */
class DateTest extends \unittest\TestCase {
  public
    $nowTime  = 0,
    $nowDate  = null,
    $refDate  = null;

  protected $tz= null;
  
  /**
   * Set up this test
   *
   * @return void
   */
  public function setUp() {
    $this->tz= date_default_timezone_get();
    date_default_timezone_set('GMT');
    
    $this->nowTime= time();
    $this->nowDate= new Date($this->nowTime);
    $this->refDate= new Date('1977-12-14 11:55');
  }

  /**
   * Tear down test.
   *
   * @return void
   */
  public function tearDown() {
    date_default_timezone_set($this->tz);
  }
  
  /**
   * Helper method
   *
   * @param   string expected
   * @param   util.Date date
   * @param   string error default 'datenotequal'
   * @return  bool
   */
  public function assertDateEquals($expected, $date, $error= 'datenotequal') {
    $this->assertEquals( 
      $expected,
      date_format($date->getHandle(), 'Y-m-d\TH:i:sP'),
      $error
    );
  }
  
  #[@test]
  public function constructorParseWithoutTz() {
    $this->assertEquals(true, new Date('2007-01-01 01:00:00 Europe/Berlin') instanceof Date);
  }
  
  #[@test]
  public function constructorUnixtimestampWithoutTz() {
    $this->assertDateEquals('2007-08-23T12:35:47+00:00', new Date(1187872547));
  }
  
  #[@test]
  public function constructorUnixtimestampWithTz() {
    $this->assertDateEquals('2007-08-23T14:35:47+02:00', new Date(1187872547, new TimeZone('Europe/Berlin')));
  }
  
  #[@test]
  public function constructorParseTz() {
    $date= new Date('2007-01-01 01:00:00 Europe/Berlin');
    $this->assertEquals('Europe/Berlin', $date->getTimeZone()->getName());
    $this->assertDateEquals('2007-01-01T01:00:00+01:00', $date);
    
    $date= new Date('2007-01-01 01:00:00 Europe/Berlin', new TimeZone('Europe/Athens'));
    $this->assertEquals('Europe/Berlin', $date->getTimeZone()->getName());
    $this->assertDateEquals('2007-01-01T01:00:00+01:00', $date);

    $date= new Date('2007-01-01 01:00:00', new TimeZone('Europe/Athens'));
    $this->assertEquals('Europe/Athens', $date->getTimeZone()->getName());
    $this->assertDateEquals('2007-01-01T01:00:00+02:00', $date);
  }
  
  #[@test]
  public function noDiscreteTimeZone() {
    $date= new Date('2007-11-04 14:32:00+1000');
    $this->assertEquals('+1000', $date->getOffset());
    $this->assertEquals(36000, $date->getOffsetInSeconds());
  }
  
  #[@test]
  public function constructorParseNoTz() {
    $date= new Date('2007-01-01 01:00:00', new TimeZone('Europe/Athens'));
    $this->assertEquals('Europe/Athens', $date->getTimeZone()->getName());
    
    $date= new Date('2007-01-01 01:00:00');
    $this->assertEquals('GMT', $date->getTimeZone()->getName());
  }
  
  #[@test]
  public function testDate() {
    $this->assertEquals($this->nowDate->getTime(), $this->nowTime);
    $this->assertEquals($this->nowDate->toString('r'), date('r', $this->nowTime));
    $this->assertTrue($this->nowDate->isAfter(new Date('yesterday')));
    $this->assertTrue($this->nowDate->isBefore(new Date('tomorrow')));
  }
  
  #[@test]
  public function preUnixEpoch() {
    $this->assertDateEquals('1969-12-31T00:00:00+00:00', new Date('31.12.1969 00:00 GMT'));
  }

  /**
   * Test dates before the year 1582 are 11 days off.
   *
   * Quoting Wikipedia:
   * The last day of the Julian calendar was Thursday October 4, 1582 and 
   * this was followed by the first day of the Gregorian calendar, Friday 
   * October 15, 1582 (the cycle of weekdays was not affected).
   *
   * @see   http://en.wikipedia.org/wiki/Gregorian_calendar
   */
  #[@test, @ignore('PHP date functions do not support dates before 1753')]
  public function pre1582() {
    $this->assertDateEquals('1499-12-21T00:00:00+00:00', new Date('01.01.1500 00:00 GMT'));
  }

  /**
   * Test dates before the year 1752 are 11 days off.
   *
   * Quoting Wikipedia:
   * The Kingdom of Great Britain and thereby the rest of the British 
   * Empire (including the eastern part of what is now the United States) 
   * adopted the Gregorian calendar in 1752 under the provisions of 
   * the Calendar Act 1750; by which time it was necessary to correct 
   * by eleven days (Wednesday, September 2, 1752 being followed by 
   * Thursday, September 14, 1752) to account for February 29, 1700 
   * (Julian). 
   *
   * @see   http://en.wikipedia.org/wiki/Gregorian_calendar
   */
  #[@test, @ignore('PHP date functions do not support dates before 1753')]
  public function calendarAct1750() {
    $this->assertDateEquals('1753-01-01T00:00:00+00:00', new Date('01.01.1753 00:00 GMT'));
    $this->assertDateEquals('1751-12-21T00:00:00+00:00', new Date('01.01.1752 00:00 GMT'));
  }

  #[@test]
  public function anteAndPostMeridiem() {
    $this->assertEquals(1, (new Date('May 28 1980 1:00AM'))->getHours(), '1:00AM != 1h');
    $this->assertEquals(0, (new Date('May 28 1980 12:00AM'))->getHours(), '12:00AM != 0h');
    $this->assertEquals(13, (new Date('May 28 1980 1:00PM'))->getHours(), '1:00PM != 13h');
    $this->assertEquals(12, (new Date('May 28 1980 12:00PM'))->getHours(), '12:00PM != 12h');
  }
  
  #[@test]
  public function anteAndPostMeridiemInMidage() {
    $this->assertEquals(1, (new Date('May 28 1580 1:00AM'))->getHours(), '1:00AM != 1h');
    $this->assertEquals(0, (new Date('May 28 1580 12:00AM'))->getHours(), '12:00AM != 0h');
    $this->assertEquals(13, (new Date('May 28 1580 1:00PM'))->getHours(), '1:00PM != 13h');
    $this->assertEquals(12, (new Date('May 28 1580 12:00PM'))->getHours(), '12:00PM != 12h');
  }
  
  #[@test]
  public function dateCreate() {
    
    // Test with a date before 1971
    $this->assertEquals(-44668800, Date::create(1968, 8, 2, 0, 0, 0)->getTime());
  }
  
  #[@test]
  public function pre1970() {
    $this->assertDateEquals('1969-02-01T00:00:00+00:00', new Date('01.02.1969'));
    $this->assertDateEquals('1969-02-01T00:00:00+00:00', new Date('1969-02-01'));
    $this->assertDateEquals('1969-02-01T00:00:00+00:00', new Date('1969-02-01 12:00AM'));
  }
  
  #[@test]
  public function serialization() {
    $original= new Date('2007-07-18T09:42:08 Europe/Athens');
    $copy= unserialize(serialize($original));
    $this->assertEquals($original, $copy);
  }
  
  #[@test]
  public function timeZoneSerialization() {
    date_default_timezone_set('Europe/Athens');
    $date= new Date('2007-11-20 21:45:33 Europe/Berlin');
    $this->assertEquals('Europe/Berlin', $date->getTimeZone()->getName());
    $this->assertEquals('+0100', $date->getOffset());
    
    $copy= unserialize(serialize($date));
    $this->assertEquals('+0100', $copy->getOffset());
  }
  
  #[@test]
  public function handlingOfTimezone() {
    $date= new Date('2007-07-18T09:42:08 Europe/Athens');

    $this->assertEquals('Europe/Athens', $date->getTimeZone()->getName());
    $this->assertEquals(3 * 3600, $date->getTimeZone()->getOffsetInSeconds($date));
  }

  /**
   * Provides values for supportedFormatTokens() test
   *
   * @return var[]
   */
  public function formatTokens() {
    return [
      //    input   , expect
      ['%Y'    , '1977'],
      ['%D %T' , '12/14/1977 11:55:00'],
      ['%C'    , '77'],
      ['%e'    , '14'],
      ['%G'    , '1977'],
      ['%H'    , '11'],
      ['%I'    , '11'],
      ['%j'    , '347'],
      ['%m'    , '12'],
      ['%M'    , '55'],
      ['%n'    , "\n"],
      ['%r'    , '11:55:00am'],
      ['%R'    , '11:55:00'],
      ['%S'    , '00'],
      ['%t'    , "\t"],
      ['%u'    , '3'],
      ['%V'    , '50'],
      ['%W'    , '50'],
      ['%w'    , '3'],
      ['%y'    , '77'],
      ['%Z'    , '+0000'],
      ['%z'    , '+0000'],
      ['%%'    , '%']
    ];
  }

  #[@test, @values('formatTokens')]
  public function supportedFormatTokens($input, $expect) {
    $this->assertEquals($expect, $this->refDate->format($input));
  }
  
  #[@test]
  public function unsupportedFormatToken() {
    $this->assertEquals('%b', $this->refDate->format('%b'));
  }
  
  #[@test]
  public function testTimestamp() {
    date_default_timezone_set('Europe/Berlin');
    
    $d1= new Date('1980-05-28 06:30:00 Europe/Berlin');
    $d2= new Date(328336200);
    
    $this->assertEquals($d1, $d2);
    $this->assertEquals($d2, new Date($d2->toString()));
  }
  
  #[@test]
  public function testTimestampWithTZ() {
    $d= new Date(328336200, new TimeZone('Australia/Sydney'));
    $this->assertEquals('Australia/Sydney', $d->getTimeZone()->getName());
  }
  
  /**
   * Test PHP Bug #42910 - timezone should not fallback to default
   * timezone if it actually is unknown.
   */
  #[@test, @ignore, @expect(IllegalStateException::class)]
  public function emptyTimeZoneNameIfUnknown() {
  
    // Specific timezone id unknown, can be Europe/Paris, Europe/Berlin, ...
    $date= new Date('1980-05-28 06:30:00+0200');
    $this->assertNotEquals('GMT', $date->getTimeZone()->getName());
  }
  
  #[@test]
  public function toStringOutput() {
    $date= new Date('2007-11-10 20:15+0100');
    $this->assertEquals('2007-11-10 20:15:00+0100', $date->toString());
    $this->assertEquals('2007-11-10 19:15:00+0000', $date->toString(Date::DEFAULT_FORMAT, new TimeZone(null)));
  }
  
  #[@test]
  public function toStringOutputPreserved() {
    $date= unserialize(serialize(new Date('2007-11-10 20:15+0100')));
    $this->assertEquals('2007-11-10 20:15:00+0100', $date->toString());
    $this->assertEquals('2007-11-10 19:15:00+0000', $date->toString(Date::DEFAULT_FORMAT, new TimeZone(null)));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function malformedInputString() {
    new Date('@@not-a-date@@');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function monthExceeded() {
    new Date('30.99.2010');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function dayExceeded() {
    new Date('99.30.2010');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function unknownTimeZoneNameInString() {
    new Date('14.12.2010 11:55:00 Europe/Karlsruhe');
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function unknownTimeZoneOffsetInString() {
    new Date('14.12.2010 11:55:00+9999');
  }

  #[@test]
  public function constructorBrokenAfterException() {
    Date::now();
    try {
      new Date('bogus');
      $this->fail('No exception raised', null, IllegalArgumentException::class);
    } catch (\lang\IllegalArgumentException $expected) { }
    Date::now();
  }
  
  #[@test, @expect(IllegalArgumentException::class)]
  public function dateCreateWithAllInvalidArguments() {
    Date::create('', '', '', '', '', '');
  }
  
  #[@test, @expect(IllegalArgumentException::class)]
  public function dateCreateWithInvalidArgumentsExceptTimeZone() {
    Date::create('', '', '', '', '', '', new TimeZone('UTC'));
  }
  
  #[@test]
  public function createDateFromStaticNowFunctionWithoutParam() {
    $this->assertEquals(true, Date::now() instanceof Date);
  }
  
  #[@test]
  public function createDateFromStaticNowFunctionWithZimeZone() {
    $d= Date::now(new TimeZone('Australia/Sydney'));
    $this->assertEquals('Australia/Sydney', $d->getTimeZone()->getName());
  }

  #[@test]
  public function createDateFromTime() {
    $date= new Date('19.19');
    $this->assertEquals(strtotime('19.19'), $date->getTime());
  }

  #[@test]
  public function testValidUnixTimestamp() {
    $this->assertDateEquals('1970-01-01T00:00:00+00:00', new Date(0));
    $this->assertDateEquals('1970-01-01T00:00:00+00:00', new Date('0'));

    $this->assertDateEquals('1970-01-01T00:00:01+00:00', new Date(1));
    $this->assertDateEquals('1970-01-01T00:00:01+00:00', new Date('1'));

    $this->assertDateEquals('1969-12-31T23:59:59+00:00', new Date(-1));
    $this->assertDateEquals('1969-12-31T23:59:59+00:00', new Date('-1'));

    $this->assertDateEquals('1969-12-20T10:13:20+00:00', new Date(-1000000));
    $this->assertDateEquals('1969-12-20T10:13:20+00:00', new Date('-1000000'));

    $this->assertDateEquals('1970-01-12T13:46:40+00:00', new Date(1000000));
    $this->assertDateEquals('1970-01-12T13:46:40+00:00', new Date('1000000'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function testInvalidUnixTimestamp() {
    new Date('+1000000');
  }

  #[@test]
  public function microseconds() {
    $this->assertEquals(393313, (new Date('2019-07-03 15:18:10.393313'))->getMicroSeconds());
  }
}
