<?php namespace util;

/**
 * Date math functions
 *
 * @ext   ext/date
 * @test  xp://net.xp_framework.unittest.util.DateMathTest
 * @see   xp://util.Date
 */
abstract class DateMath {

  /** Diff two date objects. Only full units are returned */
  public static function diff(TimeInterval $interval, Date $date1, Date $date2): int {
    if ($date1->getOffsetInSeconds() != $date2->getOffsetInSeconds()) {
  
      // Convert date2 to same timezone as date1. To work around PHP bug #45038, 
      // not just take the timezone of date1, but construct a new one which will 
      // have a timezone ID - which is required for this kind of computation.
      $tz= new TimeZone(timezone_name_from_abbr(
        '', 
        $date1->getOffsetInSeconds(), 
        $date1->toString('I')
      ));

      // Now, convert both dates to the same time (actually we only need to convert the
      // second one, as the first will remain in the same timezone)
      $date2= $tz->translate($date2);
    }
    
    // Then cut off timezone, by setting both to GMT
    $date1= DateUtil::setTimeZone($date1, new TimeZone('GMT'));
    $date2= DateUtil::setTimeZone($date2, new TimeZone('GMT'));
    
    switch ($interval) {
      case TimeInterval::$YEAR: {
        return -($date1->getYear()- $date2->getYear());
      }
      
      case TimeInterval::$MONTH: {
        return -(
          (($date1->getYear()- $date2->getYear()) * 12) +
          ($date1->getMonth()- $date2->getMonth())
        );
      }
      
      case TimeInterval::$DAY: {
        return -(intval($date1->getTime() / 86400)- intval($date2->getTime() / 86400));
      }
      
      case TimeInterval::$HOURS: {
        return -(intval($date1->getTime() / 3600)- intval($date2->getTime() / 3600));
      }

      case TimeInterval::$MINUTES: {
        return -(intval($date1->getTime() / 60)- intval($date2->getTime() / 60));
      }

      case TimeInterval::$SECONDS: {
        return -($date1->getTime()- $date2->getTime());
      }
    }
  }
}
