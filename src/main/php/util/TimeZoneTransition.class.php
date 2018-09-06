<?php namespace util;

use lang\{Value, IllegalArgumentException};

/**
 * Represent a timezone transition.
 *
 * @see   xp://util.TimeZone
 * @test  xp://net.xp_framework.unittest.util.TimeZoneTest
 */
class TimeZoneTransition implements Value {
  private $tz, $date, $isDst, $offset, $abbr;

  /**
   * Constructor
   *
   * @param  util.TimeZone $tz
   * @param  util.Date $date
   * @param  bool $isDst Whether this is daylight savings time
   * @param  int $offset Offset in seconds
   * @param  string $abbr Abbreviation, e.g. "CEST"
   */
  public function __construct(TimeZone $tz, Date $date, $isDst, $offset, $abbr) {
    $this->tz= $tz;
    $this->date= $date;
    $this->isDst= $isDst;
    $this->offset= $offset;
    $this->abbr= $abbr;
  }
  
  /**
   * Retrieve the next timezone transition for the timezone tz
   * after date date.
   *
   * @param  util.TimeZone $tz
   * @param  util.Date $date
   * @return self
   * @throws lang.IllegalArgumentException if timezone has no transitions
   */
  public static function nextTransition(TimeZone $tz, Date $date) {
    $ts= $date->getTime();
    foreach (timezone_transitions_get($tz->getHandle()) as $t) {
      if ($t['ts'] > $ts) {
        return new self($tz, new Date($t['ts']), $t['isdst'], $t['offset'], $t['abbr']);
      }
    }
    throw new IllegalArgumentException('Timezone '.$this->tz->getName().' does not have DST transitions.');
  }
  
  /**
   * Retrieve the previous timezone transition for the timezone tz
   * before date date.
   *
   * @param  util.TimeZone $tz
   * @param  util.Date $date
   * @return self
   * @throws lang.IllegalArgumentException if timezone has no transitions
   */
  public static function previousTransition(TimeZone $tz, Date $date) {
    $ts= $date->getTime();
    foreach (timezone_transitions_get($tz->getHandle()) as $t) {
      if ($t['ts'] >= $ts) {
        return new self($tz, new Date($l['ts']), $l['isdst'], $l['offset'], $l['abbr']);
      }
      $l= $t;
    }
    throw new IllegalArgumentException('Timezone '.$this->tz->getName().' does not have DST transitions.');
  }
  
  /**
   * Seek to the next timezone transition and return a new transition instance
   *
   * @throws lang.IllegalArgumentException if timezone has no transitions
   * @return self
   */
  public function next() {
    return self::nextTransition($this->tz, $this->date);
  }

  /**
   * Seek to the previous timezone transition and return a new transition instance
   *
   * @throws lang.IllegalArgumentException if timezone has no transitions
   * @return self
   */
  public function previous() {
    return self::previousTransition($this->tz, $this->date);
  }
  
  /**
   * Gets timezone
   *
   * @deprecated Use timezone() instead!
   * @return util.TimeZone
   */
  public function getTz() { return $this->tz; }

  /**
   * Gets date
   *
   * @deprecated Use date() instead!
   * @return util.Date
   */
  public function getDate() { return $this->date; }

  /** @return util.TimeZone */
  public function timezone() { return $this->tz; }

  /** @return util.Date */
  public function date() { return $this->date; }

  /** @return bool */
  public function isDst() { return $this->isDst; }

  /** @return int */
  public function offset() { return $this->offset; }

  /** @return string */
  public function abbr() { return $this->abbr; }

  /** @return string */
  public function difference() {
    $h= (int)($this->offset / 3600);
    $m= $this->offset - $h * 3600;
    return $this->offset > 0 ? sprintf('+%02d%02d', $h, $m) : sprintf('-%02d%02d', -$h, -$m);
  }

  /**
   * Compare this timezone to a give value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->toString() <=> $value->toString() : 1;
  }

  /**
   * Create a hashcode
   *
   * @return string
   */
  public function hashCode() {
    return md5($this->toString());
  }

  /**
   * Create string representation of transition
   *
   * @return  string
   */
  public function toString() {
    $s= nameof($this)."@{\n";
    $s.= '  transition at: '.$this->date->toString()."\n";
    $s.= sprintf('  transition to: %s (%s), %s',
      $this->offset,
      $this->abbr,
      ($this->isDst ? 'DST' : 'non-DST')
    );
    return $s."\n}";
  }
}
