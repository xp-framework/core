<?php namespace net\xp_framework\unittest\text;

use text\DateFormat;
use util\Date;
use lang\FormatException;
use lang\IllegalArgumentException;

/**
 * TestCase
 *
 * @see      xp://text.DateFormat
 */
class DateFormatTest extends \unittest\TestCase {

  #[@test]
  public function parseUsFormat() {
    $this->assertEquals(
      new Date('2009-01-01'),
      (new DateFormat('%Y-%m-%d'))->parse('2009-01-01')
    );
  }

  #[@test]
  public function formatUsFormat() {
    $this->assertEquals(
      '2009-12-14',
      (new DateFormat('%Y-%m-%d'))->format(new Date('2009-12-14'))
    );
  }

  #[@test]
  public function parseUsFormatWithTime() {
    $this->assertEquals(
      new Date('2009-12-14 14:24:36'),
      (new DateFormat('%Y-%m-%d %I:%M:%S %p'))->parse('2009-12-14 02:24:36 PM')
    );
  }

  #[@test]
  public function formatUsFormatWithTime() {
    $this->assertEquals(
      '2009-12-14 02:24:36 PM',
      (new DateFormat('%Y-%m-%d %I:%M:%S %p'))->format(new Date('2009-12-14 14:24:36'))
    );
  }

  #[@test]
  public function parseUsFormatWith24HourTime() {
    $this->assertEquals(
      new Date('2009-12-14 14:00:01'),
      (new DateFormat('%Y-%m-%d %H:%M:%S'))->parse('2009-12-14 14:00:01')
    );
  }

  #[@test]
  public function formatUsFormatWith24HourTime() {
    $this->assertEquals(
      '2009-12-14 14:24:36',
      (new DateFormat('%Y-%m-%d %H:%M:%S'))->format(new Date('2009-12-14 14:24:36'))
    );
  }

  #[@test]
  public function parseEuFormat() {
    $this->assertEquals(
      new Date('2009-12-14'),
      (new DateFormat('%d.%m.%Y'))->parse('14.12.2009')
    );
  }

  #[@test]
  public function formatEuFormat() {
    $this->assertEquals(
      '09.01.2009',
      (new DateFormat('%d.%m.%Y'))->format(new Date('2009-01-09'))
    );
  }

  #[@test]
  public function parseEuFormatWithTime() {
    $this->assertEquals(
      new Date('2009-12-14 11:45:00'),
      (new DateFormat('%d.%m.%Y %H:%M:%S'))->parse('14.12.2009 11:45:00')
    );
  }

  #[@test]
  public function formatEuFormatWithTime() {
    $this->assertEquals(
      '14.12.2009 11:45:00',
      (new DateFormat('%d.%m.%Y %H:%M:%S'))->format(new Date('2009-12-14 11:45:00'))
    );
  }

  #[@test]
  public function parseDateWithTimeZoneName() {
    $this->assertEquals(
      new Date('2009-12-14 11:45:00', new \util\TimeZone('Europe/Berlin')),
      (new DateFormat('%Y-%m-%d %H:%M:%S %z'))->parse('2009-12-14 11:45:00 Europe/Berlin')
    );
  }

  #[@test]
  public function formatDateWithTimeZoneName() {
    $this->assertEquals(
      '2009-12-14 11:45:00 Europe/Berlin',
      (new DateFormat('%Y-%m-%d %H:%M:%S %z'))->format(new Date('2009-12-14 11:45:00', new \util\TimeZone('Europe/Berlin')))
    );
  }

  #[@test]
  public function parseDateWithTimeZoneOffset() {
    $this->assertEquals(
      new Date('2009-12-14 11:45:00-0800'),
      (new DateFormat('%Y-%m-%d %H:%M:%S%Z'))->parse('2009-12-14 11:45:00-0800')
    );
  }

  #[@test]
  public function formatDateWithTimeZoneOffset() {
    $this->assertEquals(
      '2009-12-14 11:45:00-0800',
      (new DateFormat('%Y-%m-%d %H:%M:%S%Z'))->format(new Date('2009-12-14 11:45:00-0800'))
    );
  }

  #[@test]
  public function formatLiteralPercent() {
    $this->assertEquals('%', (new DateFormat('%%'))->format(new Date()));
  }

  #[@test]
  public function parseGermanMonthNamesInInput() {
    $this->assertEquals(
      new Date('2011-03-07'),
      (new DateFormat('%d-%[month=Jan,Feb,Mrz,Apr,Mai,Jun,Jul,Aug,Sep,Okt,Nov,Dez]-%Y'))->parse('07-Mrz-2011')
    );
  }

  #[@test]
  public function formatGermanMonthNames() {
    $this->assertEquals(
      '07-Mrz-2011',
      (new DateFormat('%d-%[month=Jan,Feb,Mrz,Apr,Mai,Jun,Jul,Aug,Sep,Okt,Nov,Dez]-%Y'))->format(new Date('2011-03-07'))
    );
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function illegalToken() {
    new DateFormat('%^');
  }

  #[@test, @expect(FormatException::class)]
  public function stringTooShort() {
    (new DateFormat('%Y-%m-%d'))->parse('2004');
  }

  #[@test, @expect(FormatException::class)]
  public function formatMismatch() {
    (new DateFormat('%Y-%m-%d'))->parse('12.12.2004');
  }

  #[@test, @expect(FormatException::class)]
  public function nonNumericInput() {
    (new DateFormat('%Y'))->parse('Hello');
  }
}
