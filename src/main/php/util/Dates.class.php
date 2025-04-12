<?php namespace util;

/**
 * Dates is a helper class to handle Date objects and 
 * calculate date- and timestamps.
 *
 * @test  util.unittest.DatesTest
 * @see   https://www.php.net/manual/en/datetime.formats.relative.php Relative formats
 * @see   https://www.php.net/manual/en/dateinterval.construct.php Periods
 * @see   https://en.wikipedia.org/wiki/ISO_8601#Durations
 */
class Dates {

  /**
   * Adds a time span to a date
   *
   * @param  util.Date $date
   * @param  util.TimeSpan|int|string $span
   * @return util.Date
   */
  public static function add(Date $date, $span) {
    if ($span instanceof TimeSpan) {
      return new Date($date->getTime() + $span->getSeconds());
    } else if (is_numeric($span)) {
      return new Date($date->getTime() + $span);
    } else if ('P' === $span[0]) {
      return new Date($date->getHandle()->add(new \DateInterval($span)));
    } else {
      return new Date($date->getHandle()->add(\DateInterval::createFromDateString($span)));
    }
  }

  /**
   * Subtracts a time span from a date
   *
   * @param  util.Date $date
   * @param  util.TimeSpan|int|string $span
   * @return util.Date
   */
  public static function subtract(Date $date, $span) {
    if ($span instanceof TimeSpan) {
      return new Date($date->getTime() - $span->getSeconds());
    } else if (is_numeric($span)) {
      return new Date($date->getTime() - $span);
    } else if ('P' === $span[0]) {
      return new Date($date->getHandle()->sub(new \DateInterval($span)));
    } else {
      return new Date($date->getHandle()->sub(\DateInterval::createFromDateString($span)));
    }
  }

  /**
   * Truncates a date, leaving the field specified as the most significant field.
   *
   * @param  util.Date $date
   * @param  util.TimeInterval $interval
   * @return util.Date
   */
  public static function truncate(Date $date, TimeInterval $interval) {
    $h= $date->getHandle();
    switch ($interval) {
      case TimeInterval::$YEAR: $h->setDate($date->getYear(), 1, 1); $h->setTime(0, 0, 0); break;
      case TimeInterval::$MONTH: $h->setDate($date->getYear(), $date->getMonth(), 1); $h->setTime(0, 0, 0); break;
      case TimeInterval::$DAY: $h->setTime(0, 0, 0); break;
      case TimeInterval::$HOURS: $h->setTime($date->getHours(), 0, 0); break;
      case TimeInterval::$MINUTES: $h->setTime($date->getHours(), $date->getMinutes(), 0); break;
      case TimeInterval::$SECONDS: $h->setTime($date->getHours(), $date->getMinutes(), (int)$date->getSeconds()); break;
    }
    return new Date($h);
  }

  /**
   * Gets a date ceiling, leaving the field specified as the most significant field.
   *
   * @param  util.Date $date
   * @param  util.TimeInterval $interval
   * @return util.Date
   */
  public static function ceiling(Date $date, TimeInterval $interval) {
    $h= $date->getHandle();
    switch ($interval) {
      case TimeInterval::$YEAR: $h->setDate($date->getYear() + 1, 1, 1); $h->setTime(0, 0, 0); break;
      case TimeInterval::$MONTH: $h->setDate($date->getYear(), $date->getMonth() + 1, 1); $h->setTime(0, 0, 0); break;
      case TimeInterval::$DAY: $h->setDate($date->getYear(), $date->getMonth(), $date->getDay() + 1); $h->setTime(0, 0, 0); break;
      case TimeInterval::$HOURS: $h->setTime($date->getHours() + 1, 0, 0); break;
      case TimeInterval::$MINUTES: $h->setTime($date->getHours(), $date->getMinutes() + 1, 0); break;
      case TimeInterval::$SECONDS: $h->setTime($date->getHours(), $date->getMinutes(), (int)$date->getSeconds() + 1); break;
    }
    return new Date($h);
  }

  /**
   * Returns a TimeSpan representing the difference 
   * between the two given Date objects
   *
   * @param  util.Date $a
   * @param  util.Date $b
   * @return util.TimeSpan
   */
  public static function diff(Date $a, Date $b) {
    return new TimeSpan($a->getTime() - $b->getTime());
  }

  /**
   * Comparator method for two Date objects
   *
   * Returns a negative number if a < b, a positive number if a > b 
   * and 0 if both dates are equal
   *
   * Example usage with usort():
   * ```php
   * usort($datelist, [Dates::class, 'compare'])
   * ```
   *
   * @param  util.Date $a
   * @param  util.Date $b
   * @return int
   */
  public static function compare(Date $a, Date $b) {
    return $b->compareTo($a);
  }
} 
