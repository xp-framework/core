<?php namespace util\unittest;

use lang\IllegalArgumentException;
use unittest\{Assert, Expect, Test, Values};
use util\{Date, TimeZone};

class TimeZoneTest {

  #[Test]
  public function name() {
    Assert::equals('Europe/Berlin', (new TimeZone('Europe/Berlin'))->name());
  }

  #[Test, Values([['Europe/Berlin', '2007-08-21', 7200], ['Australia/Adelaide', '2007-01-21', 37800], ['America/New_York', '2007-08-21', -14400]])]
  public function offset_daylight($tz, $date, $offset) {
    Assert::equals($offset, (new TimeZone($tz))->offset(new Date($date)));
  }

  #[Test, Values([['Europe/Berlin', '2007-01-21', 3600], ['Australia/Adelaide', '2007-08-21', 34200], ['America/New_York', '2007-01-21', -18000]])]
  public function offset_standard($tz, $date, $offset) {
    Assert::equals($offset, (new TimeZone($tz))->offset(new Date($date)));
  }

  #[Test, Values([['Europe/Berlin', '2007-08-21', '+0200'], ['Australia/Adelaide', '2007-01-21', '+1030'], ['America/New_York', '2007-08-21', '-0400']])]
  public function difference_daylight($tz, $date, $diff) {
    Assert::equals($diff, (new TimeZone($tz))->difference(new Date($date)));
  }

  #[Test, Values([['Europe/Berlin', '2007-01-21', '+0100'], ['Australia/Adelaide', '2007-08-21', '+0930'], ['America/New_York', '2007-01-21', '-0500']])]
  public function difference_standard($tz, $date, $diff) {
    Assert::equals($diff, (new TimeZone($tz))->difference(new Date($date)));
  }

  #[Test]
  public function translate() {
    Assert::equals(
      new Date('2006-12-31 14:00:00 Europe/Berlin'),
      (new TimeZone('Europe/Berlin'))->translate(new Date('2007-01-01 00:00 Australia/Sydney'))
    );
  }
  
  #[Test]
  public function previous_transition() {
    $transition= (new TimeZone('Europe/Berlin'))->previousTransition(new Date('2007-08-23'));
    Assert::equals(true, $transition->isDst());
    Assert::equals('CEST', $transition->abbr());
    Assert::equals('+0200', $transition->difference());
    Assert::equals(new Date('2007-03-25 02:00:00 Europe/Berlin'), $transition->date());
  }
  
  #[Test]
  public function previous_previous_transition() {
    $tz= new TimeZone('Europe/Berlin');
    $transition= $tz->previousTransition(new Date('2007-08-23'));
    $previous= $transition->previous();
    Assert::false($previous->isDst());
    Assert::equals('CET', $previous->abbr());
    Assert::equals('+0100', $previous->difference());
    Assert::equals(new Date('2006-10-29 02:00:00', $tz), $previous->date());
  }

  #[Test]
  public function previous_next_transition() {
    $tz= new TimeZone('Europe/Berlin');
    $transition= $tz->previousTransition(new Date('2007-08-23'));
    $next= $transition->next();
    Assert::false($next->isDst());
    Assert::equals('CET', $next->abbr());
    Assert::equals('+0100', $next->difference());
    Assert::equals(new Date('2007-10-28 02:00:00', $tz), $next->date());
  }

  #[Test]
  public function next_transition() {
    $tz= new TimeZone('Europe/Berlin');
    $transition= $tz->nextTransition(new Date('2007-08-23'));
    Assert::equals(false, $transition->isDst());
    Assert::equals('CET', $transition->abbr());
    Assert::equals('+0100', $transition->difference());
    Assert::equals(new Date('2007-10-28 02:00:00', $tz), $transition->date());
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function unknownTimeZone() {
    new TimeZone('UNKNOWN');
  }

  #[Test, Values([['Europe/Berlin', 2022, true], ['Europe/Berlin', 1977, false], ['Europe/Berlin', 1945, true], ['Atlantic/Reykjavik', 2022, false], ['UTC', 2022, false]])]
  public function has_dst($name, $year, $expected) {
    Assert::equals($expected, TimeZone::getByName($name)->hasDst($year));
  }

  #[Test]
  public function name_used_as_hashcode() {
    Assert::equals(
      'Europe/Berlin',
      TimeZone::getByName('Europe/Berlin')->hashCode()
    );
  }

  #[Test]
  public function string_representation() {
    Assert::equals(
      'util.TimeZone("Europe/Berlin")',
      TimeZone::getByName('Europe/Berlin')->toString()
    );
  }

  #[Test, Values([['Europe/Berlin', 0], ['Europe/Paris', -1], ['America/New_York', 1]])]
  public function compare_to($name, $expected) {
    Assert::equals(
      $expected,
      TimeZone::getByName('Europe/Berlin')->compareTo(TimeZone::getByName($name))
    );
  }
}