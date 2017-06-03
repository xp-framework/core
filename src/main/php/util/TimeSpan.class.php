<?php namespace util;

use lang\{Value, IllegalArgumentException, IllegalStateException};

/**
 * Represents a span of time
 *
 * @see   xp://util.DateUtil#timespanBetween
 * @test  xp://net.xp_framework.unittest.util.TimeSpanTest
 */
class TimeSpan implements Value {
  protected $_seconds = 0;
  
  /**
   * Contructor
   *
   * @param   int secs - an amount of seconds, absolute value is used
   * @throws  lang.IllegalArgumentException in case the value given is not numeric
   */
  public function __construct($secs= 0) {
    if (!is_numeric($secs)) {
      throw new IllegalArgumentException('Given argument is not an integer: '.typeof($secs)->getName());
    }
    $this->_seconds= (int)abs($secs);
  }

  /**
   * Add a TimeSpan
   *
   * @param   util.TimeSpan... args
   * @return  util.TimeSpan
   */
  public function add(... $args) {
    foreach ($args as $span) {
      if (!$span instanceof self) {
        throw new IllegalArgumentException('Given argument is not a TimeSpan: '.typeof($span)->getName());
      }

      $this->_seconds+= $span->_seconds;
    }
    
    return $this;
  }

  /**
   * Substract a TimeSpan
   *
   * @param   util.TimeSpan... args
   * @return  util.TimeSpan
   * @throws  lang.IllegalStateException if the result would be a negative timespan
   */
  public function substract(... $args) {
    foreach ($args as $span) {
      if (!$span instanceof self) {
        throw new IllegalArgumentException('Given argument is not a TimeSpan: '.typeof($span)->getName());
      }
      if ($span->_seconds > $this->_seconds) {
        throw new IllegalStateException('Cannot subtract '.$span->toString().' from '.$this->toString());
      }

      $this->_seconds-= $span->_seconds;
    }
    
    return $this;
  }

  /**
   * Get timespan from seconds
   *
   * @param   int seconds
   * @return  util.TimeSpan
   */
  public static function seconds($seconds) {
    return new self($seconds);
  }

  /**
   * Get timespan from minutes
   *
   * @param   int minutes
   * @return  util.TimeSpan
   */
  public static function minutes($minutes) {
    return new self($minutes * 60);
  }

  /**
   * Get timespan from hours
   *
   * @param   int hours
   * @return  util.TimeSpan
   */
  public static function hours($hours) {
    return new self($hours * 3600);
  }

  /**
   * Get timespan from days
   *
   * @param   int days
   * @return  util.TimeSpan
   */
  public static function days($days) {
    return new self($days * 86400);
  }

  /**
   * Get timespan from weeks
   *
   * @param   int weeks
   * @return  util.TimeSpan
   */
  public static function weeks($weeks) {
    return new self($weeks * 604800);
  }

  /**
   * Returns this span of time in seconds
   *
   * @return  int
   */
  public function getSeconds() {
    return $this->_seconds;
  }

  /**
   * Returns the amount of 'whole' seconds in this 
   * span of time
   *
   * @return  int
   */
  public function getWholeSeconds() {
    return $this->_seconds % 60;
  }
  
  /**
   * Return an amount of minutes less than or equal
   * to this span of time
   *
   * @return  int
   */
  public function getMinutes() {
    return (int)floor($this->_seconds / 60);
  }
  
  /**
   * Returns a float value representing this span of time
   * in minutes
   *
   * @return  float
   */
  public function getMinutesFloat() {
    return $this->_seconds / 60;
  }

  /**
   * Returns the amount of 'whole' minutes in this 
   * span of time
   *
   * @return  int
   */
  public function getWholeMinutes() {
    return (int)floor(($this->_seconds % 3600) / 60);
  }
  
  /**
   * Returns an amount of hours less than or equal
   * to this span of time
   *
   * @return  int
   */
  public function getHours() {
    return (int)floor($this->_seconds / 3600);
  }
  
  /**
   * Returns a float value representing this span of time
   * in hours
   *
   * @return  float
   */
  public function getHoursFloat() {
    return $this->_seconds / 3600;
  }

  /**
   * Returns the amount of 'whole' hours in this 
   * span of time
   *
   * @return  int
   */
  public function getWholeHours() {
    return (int)floor(($this->_seconds % 86400) / 3600);
  }
  
  /**
   * Returns an amount of days less than or equal
   * to this span of time
   *
   * @return  int
   */
  public function getDays() {
    return (int)floor($this->_seconds / 86400);
  }
  
  /**
   * Returns a float value representing this span of time
   * in days
   *
   * @return  float
   */
  public function getDaysFloat() {
    return $this->_seconds / 86400;
  }

  /**
   * Returns the amount of 'whole' days in this 
   * span of time
   *
   * @return  int
   */
  public function getWholeDays() {
    return $this->getDays();
  }

  /**
   * Format timespan
   *
   * Format tokens are:
   * <pre>
   * %s   - seconds
   * %w   - 'whole' seconds
   * %m   - minutes
   * %M   - minutes (float)
   * %j   - 'whole' minutes
   * %h   - hours
   * %H   - hours (float)
   * %y   - 'whole' hours
   * %d   - days
   * %D   - days (float)
   * %e   - 'whole' days
   * </pre>
   *
   * @param   string format
   * @return  string the formatted timespan
   */
  public function format($format) {
    $return= '';
    $o= 0; $l= strlen($format);
    while (false !== ($p= strcspn($format, '%', $o))) {
      $return.= substr($format, $o, $p);
      if (($o+= $p+ 2) <= $l) {
        switch ($format{$o- 1}) {
          case 's':
            $return.= $this->getSeconds();
            break;
          case 'w':
            $return.= $this->getWholeSeconds();
            break;
          case 'm':
            $return.= $this->getMinutes();
            break;
          case 'M':
            $return.= sprintf('%.2f', $this->getMinutesFloat());
            break;
          case 'j':
            $return.= $this->getWholeMinutes();
            break;
          case 'h':
            $return.= $this->getHours();
            break;
          case 'H':
            $return.= sprintf('%.2f', $this->getHoursFloat());
            break;
          case 'y':
            $return.= $this->getWholeHours();
            break;
          case 'd':
            $return.= $this->getDays();
            break;
          case 'D':
            $return.= sprintf('%.2f', $this->getDaysFloat());
            break;
          case 'e':
            $return.= $this->getWholeDays();
            break;
          case '%':
            $return.= '%';
            break;
          default:
            $o--;
        }
      }
    }
   
    return $return;
  }

  /**
   * Compares this timespan to another value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->_seconds <=> $value->_seconds : 1;
  }

  /**
   * Create a hashcode
   *
   * @return string
   */
  public function hashCode() {
    return 'S'.$this->_seconds;
  }

  /**
   * Creates a string representation
   *
   * @see     xp://util.TimeSpan#format
   * @param   string format, defaults to '%ed, %yh, %jm, %ws'
   * @return  string
   */
  public function toString($format= '%ed, %yh, %jm, %ws') {
    return $this->format($format);
  }
}
