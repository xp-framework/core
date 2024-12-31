<?php namespace util;

use DateTime, DateTimeZone;
use lang\{IllegalArgumentException, IllegalStateException, Value};

/**
 * The class Date represents a specific instant in time.
 *
 * @test  net.xp_framework.unittest.util.DateTest
 */
class Date implements Value {
  const DEFAULT_FORMAT= 'Y-m-d H:i:sO';

  /** @type php.DateTime */
  private $handle;

  static function __static() {
    if (!date_default_timezone_set(ltrim(get_cfg_var('date.timezone'), ':'))) {
      throw new IllegalStateException('[xp::core] date.timezone not configured properly.');
    }
  }

  /**
   * Constructor. Creates a new date object through either a
   *
   * - integer - interpreted as timestamp
   * - string - parsed into a date
   * - php.DateTime object - will be used as is
   * - NULL - creates a date representing the current instance
   *
   * Timezone assignment works through these rules:
   *
   * - If the time is given as string and contains a parseable timezone identifier
   *   that one is used.
   * - If no timezone could be determined, the timezone given by the
   *   second parameter is used
   * - If no timezone has been given as second parameter, the system's default
   *   timezone is used.
   *
   * @param  ?int|float|string|DateTime $in
   * @param  ?string|util.TimeZone $timezone
   * @throws lang.IllegalArgumentException in case the date is unparseable
   */
  public function __construct($in= null, $timezone= null) {
    if (null === $timezone) {
      $tz= null;
    } else if ($timezone instanceof TimeZone) {
      $tz= $timezone->getHandle();
    } else {
      $tz= new DateTimeZone($timezone);
    }

    if (null === $in) {
      $this->handle= date_create('now', $tz);
    } else if ($in instanceof DateTime) {
      $this->handle= $in;
    } else if (is_int($in) || (string)(int)$in === $in) {

      // Specially mark timestamps for parsing (we assume here that strings
      // containing only digits are timestamps)
      $this->handle= date_create('@'.$in);
      $tz && date_timezone_set($this->handle, $tz);
    } else if (is_float($in)) {

      // Timestamps with microseconds are defined as `"@" "-"? [0-9]+ "." [0-9]{0,6}`,
      // see https://www.php.net/manual/en/datetime.formats.php#datetime.formats.relative
      $this->handle= date_create('@'.sprintf('%.6f', $in));
      $tz && date_timezone_set($this->handle, $tz);
    } else {
      if (false === ($this->handle= date_create($in ?? 'now', $tz))) {
        throw new IllegalArgumentException('Given argument is neither a timestamp nor a well-formed timestring: '.Objects::stringOf($in));
      }
    }
  }

  /** Returns a hashcode for this object */
  public function hashCode(): string {
    return $this->handle->format('U');
  }

  /** Retrieve handle of underlying DateTime object. */
  public function getHandle(): DateTime {
    return clone $this->handle;
  }
  
  /** @return [:string] */
  public function __serialize() {
    return ['value' => date_format($this->handle, self::DEFAULT_FORMAT)];
  }
  
  /** @param [:string] $data */
  public function __unserialize($data) {
    $this->handle= date_create_from_format(self::DEFAULT_FORMAT, $data['value']);
  }

  /**
   * Construct a date object out of it's time values If a timezone string
   * the date will be set into that zone - defaulting to the system's
   * default timezone of none is given.
   *
   * @param  int $year
   * @param  int $month
   * @param  int $day
   * @param  int $hour
   * @param  int $minute
   * @param  int $second
   * @param  ?string|util.TimeZone $timezone
   * @return self
   */
  public static function create($year, $month, $day, $hour, $minute, $second, $timezone= null): self {
    if (null === $timezone) {
      $date= date_create();
    } else if ($timezone instanceof TimeZone) {
      $date= date_create('now', $timezone->getHandle());
    } else {
      $date= date_create('now', new DateTimeZone($timezone));
    }

    try {
      $r= date_date_set($date, $year, $month, $day) && date_time_set($date, $hour, $minute, $second);
    } catch (\Throwable $e) {
      $r= false;
    }

    if (!$r) {
      $e= new IllegalArgumentException(sprintf(
        'One or more given arguments are not valid: $year=%s, $month=%s, $day= %s, $hour=%s, $minute=%s, $second=%s',
        $year,
        $month,
        $day,
        $hour,
        $minute,
        $second 
      ));
      \xp::gc(__FILE__);
      throw $e;
    }
    
    return new self($date);
  }
  
  /** Indicates whether another values equals this date. */
  public function equals($cmp): bool {
    return $cmp instanceof self && $this->getTime() === $cmp->getTime();
  }
  
  /** @param ?string|util.TimeZone $timezone */
  public static function now($timezone= null): self {
    return new self(null, $timezone);
  }
  
  /** Compare this date to another date */
  public function compareTo($cmp): int {
    return $cmp instanceof self ? $cmp->getTime() - $this->getTime() : -1;
  }
  
  /** Checks whether this date is before a given date */
  public function isBefore(Date $date): bool {
    return $this->getTime() < $date->getTime();
  }

  /** Checks whether this date is after a given date */
  public function isAfter(Date $date): bool {
    return $this->getTime() > $date->getTime();
  }
  
  /** Retrieve Unix-Timestamp for this date */
  public function getTime(): int { return date_timestamp_get($this->handle); }

  /** Get microseconds */
  public function getMicroSeconds(): int { return $this->handle->format('u'); }

  /** Get seconds */
  public function getSeconds(): int { return $this->handle->format('s'); }

  /** Get minutes */
  public function getMinutes(): int { return $this->handle->format('i'); }

  /** Get hours */
  public function getHours(): int { return $this->handle->format('G'); }

  /** Get day */
  public function getDay(): int { return $this->handle->format('d'); }

  /** Get month */
  public function getMonth(): int { return $this->handle->format('m'); }

  /** Get year */
  public function getYear(): int { return $this->handle->format('Y'); }

  /** Get day of year */
  public function getDayOfYear(): int { return $this->handle->format('z'); }

  /** Get day of week */
  public function getDayOfWeek(): int { return $this->handle->format('w'); }
  
  /** Get timezone offset to UTC in "+MMSS" notation */
  public function getOffset(): string { return $this->handle->format('O'); }
  
  /** Get timezone offset to UTC in seconds */
  public function getOffsetInSeconds(): int { return date_offset_get($this->handle); }
  
  /** Retrieve timezone object associated with this date */
  public function getTimeZone(): Timezone {
    return new TimeZone(date_timezone_get($this->handle));
  }
  
  /**
   * Create a string representation
   *
   * @see    php://date
   * @param  string $format default Date::DEFAULT_FORMAT format-string
   * @param  ?string|util.TimeZone $timezone
   * @return string the formatted date
   */
  public function toString(string $format= self::DEFAULT_FORMAT, $timezone= null): string {
    if (null === $timezone) {
      $handle= $this->handle;
    } else if ($timezone instanceof TimeZone) {
      $handle= clone $this->handle;
      date_timezone_set($handle, $timezone->getHandle());
    } else {
      $handle= clone $this->handle;
      date_timezone_set($handle, new DateTimeZone($timezone));
    }
    return date_format($handle, $format);
  }
  
  /**
   * Format a date by the given strftime()-like format string.
   *
   * These format tokens are not supported intentionally:
   * %a, %A, %b, %B, %c, %h, %p, %U, %x, %X
   *
   * @see    php://strftime
   * @param  string $format
   * @param  ?string|util.TimeZone $timezone
   * @return string
   */
  public function format(string $format, $timezone= null): string {
    static $replace= [
      '%d' => 'd',
      '%m' => 'm',
      '%Y' => 'Y',
      '%H' => 'H',
      '%S' => 's',
      '%w' => 'w',
      '%G' => 'o',
      '%D' => 'm/d/Y',
      '%T' => 'H:i:s',
      '%z' => 'O',
      '%Z' => 'e',
      '%G' => 'o',
      '%V' => 'W',
      '%C' => 'y',
      '%e' => 'j',
      '%G' => 'o',
      '%H' => 'H',
      '%I' => 'h',
      '%j' => 'z',
      '%M' => 'i',
      '%r' => 'h:i:sa',
      '%R' => 'H:i:s',
      '%u' => 'N',
      '%V' => 'W',
      '%W' => 'W',
      '%w' => 'w',
      '%y' => 'y',
      '%Z' => 'O',
      '%t' => "\t",
      '%n' => "\n",
      '%%' => '%'
    ];

    if (null === $timezone) {
      $handle= $this->handle;
    } else if ($timezone instanceof TimeZone) {
      $handle= clone $this->handle;
      date_timezone_set($handle, $timezone->getHandle());
    } else {
      $handle= clone $this->handle;
      date_timezone_set($handle, new DateTimeZone($timezone));
    }
    return date_format($handle, strtr($format, $replace));
  }
}