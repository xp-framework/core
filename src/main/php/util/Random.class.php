<?php namespace util;

use io\IOException;
use lang\IllegalArgumentException;

/**
 * This random generator uses PHP's `random_bytes()` and `random_int()`
 * functions if available (PHP >= 7.0) and provides alternatives if
 * necessary.
 *
 * _Note_: This RNG prefers secure pseudo-random sources. This may be
 * slow; for considerably faster results, use Random::MTRAND (which
 * uses the Mersenne Twister algorithm)
 *
 * @see   http://www.math.sci.hiroshima-u.ac.jp/~m-mat/MT/emt.html
 * @see   http://sockpuppet.org/blog/2014/02/25/safely-generate-random-numbers/
 * @test  xp://net.xp_framework.unittest.util.RandomTest
 */
class Random {
  const SYSTEM  = 'system';
  const OPENSSL = 'openssl';
  const URANDOM = 'urandom';
  const MTRAND  = 'mtrand';

  const BEST    = '(best)';
  const FAST    = '(fast)';
  const SECURE  = '(secure)';

  private static $sources= [];
  private $bytes, $ints, $source;

  static function __static() {
    if (function_exists('random_bytes')) {
      self::$sources[self::SYSTEM]= ['bytes' => 'random_bytes', 'ints' => 'random_int'];
    }
    if (strncasecmp(PHP_OS, 'Win', 3) !== 0 && is_readable('/dev/urandom')) {
      self::$sources[self::URANDOM]= ['bytes' => [self::class, self::URANDOM], 'ints' => null];
    }

    // All of these above are secure pseudo-random sources
    if (!empty(self::$sources)) {
      self::$sources[self::SECURE]= &self::$sources[key(self::$sources)];
    }

    if (function_exists('openssl_random_pseudo_bytes')) {
      self::$sources[self::OPENSSL]= ['bytes' => [self::class, self::OPENSSL], 'ints' => null];
      if (!isset(self::$sources[self::SECURE])) {
        self::$sources[self::SECURE]= &self::$sources[self::OPENSSL];
      }
    }

    // The Mersenne Twister algorithm is always available
    self::$sources[self::MTRAND]= ['bytes' => [self::class, self::MTRAND], 'ints' => 'mt_rand'];

    self::$sources[self::BEST]= &self::$sources[key(self::$sources)];
    self::$sources[self::FAST]= &self::$sources[self::MTRAND];
  }

  /**
   * Creates a new random
   *
   * @param  string|string[] $sources One or more of SYSTEM, OPENSSL, URANDOM, MTRAND, BEST, FAST and SECURE
   * @throws lang.IllegalArgumentException
   */
  public function __construct($sources= self::BEST) {
    $test= is_array($sources) ? $sources : [$sources];
    foreach ($test as $source) {
      if (isset(self::$sources[$source])) {
        $this->bytes= self::$sources[$source]['bytes'];
        $this->ints= self::$sources[$source]['ints'] ?: [$this, 'random'];
        $this->source= $source;
        return;
      }
    }
    throw new IllegalArgumentException('None of the supplied sources '.implode(', ', $test).' are available');
  }

  /** Returns this random's source */
  public function source(): string { return $this->source; }

  /**
   * Implementation using OpenSSL
   *
   * @param  int $limit
   * @return string $bytes
   */
  private static function openssl($limit) {
    return openssl_random_pseudo_bytes($limit);
  }

  /**
   * Implementation reading from `/dev/urandom`
   *
   * @param  int $limit
   * @return string $bytes
   * @throws io.IOException if there is a problem accessing the urandom character device
   */
  private static function urandom($limit) {
    if (!($f= fopen('/dev/urandom', 'r'))) {
      $e= new IOException('Cannot access /dev/urandom');
      \xp::gc(__FILE__);
      throw $e;
    }

    // See http://man7.org/linux/man-pages/man2/stat.2.html
    $stat= fstat($f);
    if (($stat['mode'] & 0170000) !== 020000) {
      fclose($f);
      throw new IOException('Not a character device: /dev/urandom');
    }

    // HHVM does not have stream_set_read_buffer()!
    function_exists('stream_set_read_buffer') && stream_set_read_buffer($f, 0);
    $bytes= fread($f, $limit);
    fclose($f);
    return $bytes;
  }

  /**
   * Implementation using `mt_rand()`
   *
   * @param  int $limit
   * @return string $bytes
   */
  private static function mtrand($limit) {
    $bytes= '';
    for ($i= 0; $i < $limit; $i++) {
      $bytes.= chr((mt_rand() ^ mt_rand()) % 0xFF);
    }
    return $bytes;
  }

  /**
   * Uses source to fetch random bytes and calculates a random int from
   * that within the given minimum and maximum limits.
   *
   * @param  int $min
   * @param  int $max
   * @return int
   */
  private function random($min, $max) {
    $range= $max - $min;

    // How many bytes do we need to represent the range?
    $bits= (int)ceil(log($range, 2));
    $bytes= (int)ceil($bits / 8);
    $mask= 2 ** $bits - 1;

    do {
      for ($random= $this->bytes($bytes), $result= 0, $i= 0; $i < $bytes; $i++) {
        $result |= ord($random{$i}) << ($i * 8);
      }

      // Wrap around if negative
      $result &= $mask;
    } while ($result > $range);

    return $result + $min;
  }

  /**
   * Returns a number of random bytes
   *
   * @throws lang.IllegalArgumentException
   */
  public function bytes(int $amount): Bytes {
    if ($amount <= 0) {
      throw new IllegalArgumentException('Amount must be greater than 0');
    }
    $f= $this->bytes;
    return new Bytes($f($amount));
  }

  /**
   * Returns a random integer between the given min and max, both inclusive
   *
   * @param  int $min
   * @param  int $max
   * @return int
   * @throws lang.IllegalArgumentException
   */
  public function int($min= 0, $max= PHP_INT_MAX): int {
    if ($min >= $max) {
      throw new IllegalArgumentException('Minimum value must be lower than max');
    }
    if ($min < PHP_INT_MIN || $max > PHP_INT_MAX) {
      throw new IllegalArgumentException('Boundaries ['.$min.'..'.$max.'] out of range for integers');
    }
    $f= $this->ints;
    return $f($min, $max);
  }
}