<?php namespace util;

use lang\{IllegalArgumentException, Value};

/**
 * Time zone calculation
 *
 * ```php
 * $tz= new TimeZone('Europe/Berlin');
 * printf("Offset is %s\n", $tz->getOffset());  // -0600
 * ```
 *
 * @test    xp://net.xp_framework.unittest.util.TimeZoneTest
 * @see     php://datetime
 * @see     php://timezones
 */
class TimeZone implements Value {
  protected $tz= null;

  /**
   * Creates a new timezone from a given name.
   *
   * @param   string timezone name or NULL to use default timezone
   * @throws  lang.IllegalArgumentException if timezone is unknown
   */
  public function __construct($tz) {
    if (null === $tz) {
      $this->tz= timezone_open(date_default_timezone_get());
    } else if (is_string($tz)) {
      try {
        $this->tz= new \DateTimeZone($tz);
      } catch (\Throwable $e) {
        throw new IllegalArgumentException('Invalid timezone identifier given: '.$e->getMessage());
      }
    } else if ($tz instanceof \DateTimeZone) {
      $this->tz= $tz;
    } else {
      throw new IllegalArgumentException('Expecting NULL, a string or a DateTimeZone instance, have '.typeof($tz)->getName());
    }
  }
  
  /**
   * Retrieve handle of underlying DateTimeZone object
   *
   * @return  php.DateTimeZone
   */
  public function getHandle() {
    return clone $this->tz;
  }

  /**
   * Gets the name of the timezone
   *
   * @return  string name
   */
  public function getName() {
    return timezone_name_get($this->tz);
  }
  
  /**
   * Returns a TimeZone object by a time zone abbreviation.
   *
   * @param   string abbrev
   * @return  util.TimeZone
   */
  public static function getByName($abbrev) {
    return new self($abbrev);
  }
  
  /**
   * Get a timezone object for the machines local timezone.
   *
   * @return  util.TimeZone
   */
  public static function getLocal() {
    return new self(null);
  }

  /**
   * Retrieves the offset of the timezone
   *
   * @return  string offset
   */    
  public function getOffset($date= null) {
    $offset= $this->getOffsetInSeconds($date);
    
    $h= intval(abs($offset) / 3600);
    $m= (abs($offset)- ($h * 3600)) / 60;
    
    return sprintf('%s%02d%02d', ($offset < 0 ? '-' : '+'), $h, $m);
  }
  
  /**
   * Retrieve whether the timezone does have DST/non-DST mode
   *
   * @return  bool
   */
  public function hasDst() {
    return (bool)sizeof(timezone_transitions_get($this->tz));
  }

  /**
   * Retrieves the timezone offset to GMT. Because a timezone
   * may have different offsets when its in DST or non-DST mode,
   * a date object must be given which is used to determine whether
   * DST or non-DST offset should be returned.
   *
   * If no date is passed, current time is assumed.
   *
   * @param   util.Date date default NULL
   * @return  int offset
   */    
  public function getOffsetInSeconds($date= null) {
    return timezone_offset_get($this->tz, date_create($date instanceof Date ? $date->toString() : 'now'));
  }
  
  /**
   * Translates a date from one timezone to a date of this timezone.
   * The value of the date is not changed by this operation.
   *
   * @param   util.Date date
   * @return  util.Date
   */
  public function translate(Date $date) {
    $handle= clone $date->getHandle();
    date_timezone_set($handle, $this->tz);
    return new Date($handle);
  }

  /**
   * Retrieve date of the next timezone transition at the given
   * date for this timezone.
   *
   * @param   util.Date date
   * @return  util.TimeZoneTransition
   */
  public function previousTransition(Date $date) {
    return TimeZoneTransition::previousTransition($this, $date);
  }
  
  /**
   * Retrieve date of the previous timezone transition at the given
   * date for this timezone.
   *
   * @param   util.Date date
   * @return  util.TimeZoneTransition
   */
  public function nextTransition(Date $date) {
    return TimeZoneTransition::nextTransition($this, $date);
  }

  /**
   * Compare this timezone to a give value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->getName() <=> $value->getName() : 1;
  }

  /**
   * Create a hashcode
   *
   * @return string
   */
  public function hashCode() {
    return $this->getName();
  }

  /**
   * Create a string representation
   *
   * @return string
   */
  public function toString() {
    return nameof($this).' ("'.$this->getName().'" / '.$this->getOffset().')';
  }
}