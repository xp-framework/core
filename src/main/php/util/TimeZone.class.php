<?php namespace util;

use lang\{IllegalArgumentException, Value};

/**
 * Time zone calculation
 *
 * ```php
 * $tz= new TimeZone('Europe/Berlin');
 * printf("Difference to UTC is %s\n", $tz->difference());  // +0200
 * ```
 *
 * @test  xp://net.xp_framework.unittest.util.TimeZoneTest
 * @see   php://datetime
 * @see   php://timezones
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
  public function name() {
    return timezone_name_get($this->tz);
  }

  /**
   * Returns a TimeZone object by a time zone abbreviation.
   *
   * @param  string $abbrev
   * @return self
   */
  public static function getByName($abbrev) {
    return new self($abbrev);
  }
  
  /**
   * Get a timezone object for the machine's local timezone.
   *
   * @return self
   */
  public static function getLocal() {
    return new self(null);
  }

  /**
   * Retrieves the differnece of the timezone
   *
   * @param  util.Date $date default NULL
   * @return string offset
   */
  public function difference($date= null) {
    $offset= timezone_offset_get($this->tz, $date instanceof Date ? $date->getHandle() : date_create('now'));
    $h= (int)($offset / 3600);
    $m= (int)(($offset - $h * 3600) / 60);
    return $offset > 0 ? sprintf('+%02d%02d', $h, $m) : sprintf('-%02d%02d', -$h, -$m);
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
   * Retrieves the timezone offset to GMT. Because a timezone may have
   * different offsets when its in DST or non-DST mode, a date object
   * must be used to determine whether DST or non-DST offset should be
   * returned. If no date is passed, current time is assumed.
   *
   * @param  util.Date $date default NULL
   * @return int offset
   */
  public function offset($date= null) {
    return timezone_offset_get($this->tz, $date instanceof Date ? $date->getHandle() : date_create('now'));
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
    return $value instanceof self ? $this->name() <=> $value->name() : 1;
  }

  /**
   * Create a hashcode
   *
   * @return string
   */
  public function hashCode() {
    return $this->name();
  }

  /**
   * Create a string representation
   *
   * @return string
   */
  public function toString() {
    return nameof($this).'("'.$this->name().'")';
  }
}