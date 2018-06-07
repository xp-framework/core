<?php namespace util;

/**
 * DateUtil is a helper class to handle Date objects and 
 * calculate date- and timestamps.
 *
 * @test    xp://net.xp_framework.unittest.util.DateUtilTest
 */
abstract class DateUtil {

  /**
   * Returns a Date object which represents the date at
   * the given date at midnight.
   *
   * @param   util.Date date
   * @return  util.Date
   */
  public static function getMidnight(Date $date) {
    $hdl= $date->getHandle();
    date_time_set($hdl, 0, 0, 0);
    return new Date($hdl);
  }
  
  /**
   * Gets the last day of the month
   *
   * @param   util.Date date
   * @return  util.Date
   */
  public static function getLastOfMonth(Date $date) {
    $hdl= $date->getHandle();
    date_date_set($hdl, $date->getYear(), $date->getMonth() + 1, 0);
    return new Date($hdl);
  }
  
  /**
   * Gets the first day of the month
   *
   * @param   util.Date date
   * @return  util.Date
   */
  public static function getFirstOfMonth(Date $date) {
    $hdl= $date->getHandle();
    date_date_set($hdl, $date->getYear(), $date->getMonth(), 1);
    return new Date($hdl);
  }

  /**
   * Gets the first day of the week, with week starting on sunday
   *
   * @param   util.Date date
   * @return  util.Date
   */
  public static function getBeginningOfWeek(Date $date) {
    $hdl= $date->getHandle();
    $dow= date_format($hdl, 'w');
    date_date_set($hdl, $date->getYear(), $date->getMonth(), $date->getDay() - $dow);
    date_time_set($hdl, 0, 0, 0);
    return new Date($hdl);
  }

  /**
   * Gets the last day of the week
   *
   * @param   util.Date date
   * @return  util.Date
   */
  public static function getEndOfWeek(Date $date) {
    $hdl= $date->getHandle();
    $dow= date_format($hdl, 'w');
    date_date_set($hdl, $date->getYear(), $date->getMonth(), $date->getDay() + 6 - $dow);
    date_time_set($hdl, 23, 59, 59);
    return new Date($hdl);
  }

  /**
   * Adds a time span to a date
   *
   * @param   util.Date $date
   * @param   util.TimeSpan $span
   * @return  util.Date
   */
  public static function add(Date $date, TimeSpan $span) {
    return new Date($date->getTime() + $span->getSeconds());
  }

  /**
   * Subtracts a time span from a date
   *
   * @param   util.Date $date
   * @param   util.TimeSpan $span
   * @return  util.Date
   */
  public static function subtract(Date $date, TimeSpan $span) {
    return new Date($date->getTime() - $span->getSeconds());
  }

  /**
   * Adds a positive or negative amount of months
   *
   * @param   util.Date date
   * @param   int count default 1
   * @return  util.Date
   */
  public static function addMonths(Date $date, $count= 1) {
    $hdl= $date->getHandle();
    date_date_set($hdl, $date->getYear(), $date->getMonth() + $count, $date->getDay());
    return new Date($hdl);
  }

  /**
   * Adds a positive or negative amount of weeks
   *
   * @param   util.Date date
   * @param   int count default 1
   * @return  util.Date
   */
  public static function addWeeks(Date $date, $count= 1) {
    $hdl= $date->getHandle();
    date_date_set($hdl, $date->getYear(), $date->getMonth(), $date->getDay() + $count * 7);
    return new Date($hdl);
  }
  
  /**
   * Adds a positive or negative amount of days
   *
   * @param   util.Date date 
   * @param   int count default 1
   * @return  util.Date
   */
  public static function addDays(Date $date, $count= 1) {
    $hdl= $date->getHandle();
    date_date_set($hdl, $date->getYear(), $date->getMonth(), $date->getDay() + $count);
    return new Date($hdl);
  }
  
  /**
   * Adds a positive or negative amount of hours
   *
   * @param   util.Date date 
   * @param   int count default 1
   * @return  util.Date
   */
  public static function addHours(Date $date, $count= 1) {
    return new Date($date->getTime() + $count * 3600);
  }
  
  /**
   * Adds a positive or negative amount of minutes
   *
   * @param   util.Date date 
   * @param   int count default 1
   * @return  util.Date
   */
  public static function addMinutes(Date $date, $count= 1) {
    return new Date($date->getTime() + $count * 60);
  }

  /**
   * Adds a positive or negative amount of seconds
   *
   * @param   util.Date date 
   * @param   int count default 1
   * @return  util.Date
   */
  public static function addSeconds(Date $date, $count= 1) {
    return new Date($date->getTime() + $count);
  }
  
  /**
   * Move a date to a given timezone. Does not modify the date's
   * actual value.
   *
   * @param   util.Date date
   * @param   util.TimeZone tz
   * @return  util.Date
   */
  public static function moveToTimezone(Date $date, TimeZone $tz) {
    return $tz->translate($date);
  }
  
  /**
   * Set a given timezone for the passed date. Really modifies
   * the date as just the timezone is exchanged, no further
   * modifications are done.
   *
   * @param   util.Date date
   * @param   util.TimeZone tz
   * @return  util.Date
   */
  public static function setTimezone(Date $date, TimeZone $tz) {
    return Date::create(
      $date->getYear(),
      $date->getMonth(),
      $date->getDay(),
      $date->getHours(),
      $date->getMinutes(),
      $date->getSeconds(),
      $tz
    );
  }    

  /**
   * Returns a TimeSpan representing the difference 
   * between the two given Date objects
   *
   * @param   util.Date d1
   * @param   util.Date d2
   * @return  util.TimeSpan
   */
  public static function timeSpanBetween(Date $d1, Date $d2) {
    return new TimeSpan($d1->getTime()- $d2->getTime());
  }

  /**
   * Comparator method for two Date objects
   *
   * Returns a negative number if a < b, a positive number if a > b 
   * and 0 if both dates are equal
   *
   * Example usage with usort():
   * ```php
   * usort($datelist, ['util\\DateUtil', 'compare'])
   * ```
   *
   * @param   util.Date a
   * @param   util.Date b
   * @return  int
   */
  public static function compare(Date $a, Date $b) {
    return $b->compareTo($a);
  }
} 
