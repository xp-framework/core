<?php namespace util;

use lang\{Value, IllegalArgumentException};

/**
 * Represent a timezone transition.
 *
 * @see   xp://util.TimeZone
 * @test  xp://net.xp_framework.unittest.util.TimeZoneTest
 */
class TimeZoneTransition implements Value {
  protected
    $tz     = null,
    $date   = null,
    $isDst  = null,
    $offset = null,
    $abbr   = null;

  /**
   * Constructor
   *
   * @param   util.TimeZone tz
   * @param   util.Date date
   */
  public function __construct(TimeZone $tz, Date $date) {
    $this->tz= $tz;
    $this->date= $date;
  }
  
  /**
   * Retrieve the next timezone transition for the timezone tz
   * after date date.
   *
   * @param   util.TimeZone tz
   * @param   util.Date date
   * @return  util.TimeZoneTransition
   * @throws  lang.IllegalArgumentException if timezone has no transitions
   */
  public static function nextTransition(TimeZone $tz, Date $date) {
    $t= new self($tz, $date);
    $t->next();
    return $t;
  }
  
  /**
   * Retrieve the previous timezone transition for the timezone tz
   * before date date.
   *
   * @param   util.TimeZone tz
   * @param   util.Date date
   * @return  util.TimeZoneTransition
   * @throws  lang.IllegalArgumentException if timezone has no transitions
   */
  public static function previousTransition(TimeZone $tz, Date $date) {
    $t= new self($tz, $date);
    $t->previous();
    return $t;
  }
  
  /**
   * Seek to the next timezone transition
   *
   * @throws lang.IllegalArgumentException if timezone has no transitions
   * @return self
   */
  public function next() {
    $ts= $this->date->getTime();
    foreach (timezone_transitions_get($this->tz->getHandle()) as $t) {
      if ($t['ts'] > $ts) break;
    }
    if (!isset($t)) throw new IllegalArgumentException('Timezone '.$this->tz->getName().' does not have DST transitions.');
    
    $this->date= new Date($t['ts']);
    $this->isDst= $t['isdst'];
    $this->offset= $t['offset'];
    $this->abbr= $t['abbr'];
    return $this;
  }

  /**
   * Seek to the previous timezone transition
   *
   * @throws lang.IllegalArgumentException if timezone has no transitions
   * @return self
   */
  public function previous() {
    $ts= $this->date->getTime();
    foreach (timezone_transitions_get($this->tz->getHandle()) as $t) {
      if ($t['ts'] >= $ts) break;
      $last= $t;
    }
    if (!isset($t)) throw new IllegalArgumentException('Timezone '.$this->tz->getName().' does not have DST transitions.');

    $this->date= new Date($last['ts'], $this->date->getTimeZone());
    $this->isDst= $last['isdst'];
    $this->offset= $last['offset'];
    $this->abbr= $last['abbr'];
    return $this;
  }
  
  /**
   * Get Tz
   *
   * @return  util.TimeZone
   */
  public function getTz() {
    return $this->tz;
  }

  /**
   * Get Date
   *
   * @return  util.Date
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * Get IsDst
   *
   * @return  bool
   */
  public function isDst() {
    return $this->isDst;
  }

  /**
   * Get Offset
   *
   * @return  int
   */
  public function offset() {
    return $this->offset;
  }

  /**
   * Get Abbr
   *
   * @return  string
   */
  public function abbr() {
    return $this->abbr;
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
