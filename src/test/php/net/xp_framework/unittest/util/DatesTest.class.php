<?php namespace net\xp_framework\unittest\util;
 
use unittest\TestCase;
use util\{Date, Dates, TimeSpan, TimeInterval};

class DatesTest extends TestCase {

  #[@test]
  public function add_timespan() {
    $this->assertEquals(
      Date::create(2019, 6, 13, 12, 0, 10),
      Dates::add(Date::create(2019, 6, 13, 12, 0, 0), TimeSpan::seconds(10))
    );
  }

  #[@test]
  public function add_period() {
    $this->assertEquals(
      Date::create(2020, 6, 13, 12, 0, 0),
      Dates::add(Date::create(2019, 6, 13, 12, 0, 0), 'P1Y')
    );
  }

  #[@test]
  public function add_int() {
    $this->assertEquals(
      Date::create(2019, 6, 14, 12, 0, 0),
      Dates::add(Date::create(2019, 6, 13, 12, 0, 0), 86400)
    );
  }

  #[@test]
  public function add_string() {
    $this->assertEquals(
      Date::create(2019, 7, 13, 12, 0, 0),
      Dates::add(Date::create(2019, 6, 13, 12, 0, 0), '1 month')
    );
  }

  #[@test]
  public function subtract_timespan() {
    $this->assertEquals(
      Date::create(2019, 6, 13, 11, 59, 50),
      Dates::subtract(Date::create(2019, 6, 13, 12, 0, 0), TimeSpan::seconds(10))
    );
  }

  #[@test]
  public function subtract_period() {
    $this->assertEquals(
      Date::create(2018, 6, 13, 12, 0, 0),
      Dates::subtract(Date::create(2019, 6, 13, 12, 0, 0), 'P1Y')
    );
  }

  #[@test]
  public function subtract_int() {
    $this->assertEquals(
      Date::create(2019, 6, 12, 12, 0, 0),
      Dates::subtract(Date::create(2019, 6, 13, 12, 0, 0), 86400)
    );
  }

  #[@test]
  public function subtract_string() {
    $this->assertEquals(
      Date::create(2019, 5, 13, 12, 0, 0),
      Dates::subtract(Date::create(2019, 6, 13, 12, 0, 0), '1 month')
    );
  }

  #[@test]
  public function truncate_minutes() {
    $this->assertEquals(
      Date::create(2019, 6, 13, 12, 39, 0),
      Dates::truncate(Date::create(2019, 6, 13, 12, 39, 11), TimeInterval::$MINUTES)
    );
  }

  #[@test]
  public function truncate_hours() {
    $this->assertEquals(
      Date::create(2019, 6, 13, 12, 0, 0),
      Dates::truncate(Date::create(2019, 6, 13, 12, 39, 11), TimeInterval::$HOURS)
    );
  }

  #[@test]
  public function truncate_day() {
    $this->assertEquals(
      Date::create(2019, 6, 13, 0, 0, 0),
      Dates::truncate(Date::create(2019, 6, 13, 12, 0, 0), TimeInterval::$DAY)
    );
  }

  #[@test]
  public function truncate_month() {
    $this->assertEquals(
      Date::create(2019, 6, 1, 0, 0, 0),
      Dates::truncate(Date::create(2019, 6, 13, 12, 0, 0), TimeInterval::$MONTH)
    );
  }

  #[@test]
  public function truncate_year() {
    $this->assertEquals(
      Date::create(2019, 1, 1, 0, 0, 0),
      Dates::truncate(Date::create(2019, 6, 13, 12, 0, 0), TimeInterval::$YEAR)
    );
  }

  #[@test]
  public function ceiling_of_minutes() {
    $this->assertEquals(
      Date::create(2019, 6, 13, 12, 40, 0),
      Dates::ceiling(Date::create(2019, 6, 13, 12, 39, 11), TimeInterval::$MINUTES)
    );
  }

  #[@test]
  public function ceiling_of_hours() {
    $this->assertEquals(
      Date::create(2019, 6, 13, 13, 0, 0),
      Dates::ceiling(Date::create(2019, 6, 13, 12, 39, 11), TimeInterval::$HOURS)
    );
  }

  #[@test]
  public function ceiling_of_day() {
    $this->assertEquals(
      Date::create(2019, 6, 14, 0, 0, 0),
      Dates::ceiling(Date::create(2019, 6, 13, 12, 0, 0), TimeInterval::$DAY)
    );
  }

  #[@test]
  public function ceiling_of_month() {
    $this->assertEquals(
      Date::create(2019, 7, 1, 0, 0, 0),
      Dates::ceiling(Date::create(2019, 6, 13, 12, 0, 0), TimeInterval::$MONTH)
    );
  }

  #[@test]
  public function ceiling_of_year() {
    $this->assertEquals(
      Date::create(2020, 1, 1, 0, 0, 0),
      Dates::ceiling(Date::create(2019, 6, 13, 12, 0, 0), TimeInterval::$YEAR)
    );
  }

  #[@test]
  public function diff() {
    $this->assertEquals(
      TimeSpan::hours(1),
      Dates::diff(Date::create(2019, 6, 13, 12, 39, 1), Date::create(2019, 6, 13, 13, 39, 1))
    );
  }

  #[@test]
  public function compare_a_less_than_b() {
    $this->assertTrue(Dates::compare(new Date('1977-12-14'), new Date('1980-05-28')) < 0, 'a < b');
  }

  #[@test]
  public function compare_a_greater_than_b() {
    $this->assertTrue(Dates::compare(new Date('1980-05-28'), new Date('1977-12-14')) > 0, 'a > b');
  }

  #[@test]
  public function compare_a_equal_to_b() {
    $this->assertEquals(0, Dates::compare(new Date('1980-05-28'), new Date('1980-05-28')));
  }
}