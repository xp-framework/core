<?php namespace util;

use lang\{Runtime, IllegalStateException, IllegalArgumentException, Value};

/**
 * Secret provides a reasonable secure storage for security-sensistive
 * lists of characters, such as passwords.
 *
 * It prevents accidentially revealing them in output, by var_dump()ing,
 * echo()ing, or casting the object to array. All these cases will not
 * show the password, nor the crypt of it.
 *
 * However, it is not safe to consider this implementation secure in a crypto-
 * graphically sense, because it does not care for a very strong encryption,
 * and it does share the encryption key with all instances of it in a single
 * PHP instance.
 *
 * Hint: when using this class, you must make sure not to extract the secured string
 * and pass it to a place where an exception might occur, as it might be exposed as
 * method argument.
 *
 * As a rule of thumb: extract it from the container at the last possible location.
 *
 * @test   xp://net.xp_framework.unittest.util.OpenSSLSecretTest
 * @test   xp://net.xp_framework.unittest.util.PlainTextSecretTest
 */
class Secret implements Value {
  const BACKING_OPENSSL   = 0x02;
  const BACKING_PLAINTEXT = 0x03;

  private $id;
  private static $store   = [];
  private static $encrypt = null;
  private static $decrypt = null;

  static function __static() {
    if (Runtime::getInstance()->extensionAvailable('openssl')) {
      self::useBacking(self::BACKING_OPENSSL);
    } else {
      self::useBacking(self::BACKING_PLAINTEXT);
    }
  }

  /**
   * Switch storage algorithm backing
   *
   * @param  int $type one of BACKING_OPENSSL, BACKING_PLAINTEXT
   * @throws lang.IllegalArgumentException If illegal backing type was given
   * @throws lang.IllegalStateException If chosen backing missed a extension dependency
   * @return void
   */
  public static function useBacking($type) {
    switch ($type) {
      case self::BACKING_OPENSSL: {
        if (!Runtime::getInstance()->extensionAvailable('openssl')) {
          throw new IllegalStateException('Backing "openssl" required but extension not available.');
        }
        $key= md5(uniqid());
        $iv= substr(md5(uniqid()), 0, openssl_cipher_iv_length('des'));

        return self::setBacking(
          function($value) use ($key, $iv) { return openssl_encrypt($value, 'DES', $key,  0, $iv); },
          function($value) use ($key, $iv) { return openssl_decrypt($value, 'DES', $key,  0, $iv); }
        );
      }

      case self::BACKING_PLAINTEXT: {
        return self::setBacking(
          function($value) { return base64_encode($value); },
          function($value) { return base64_decode($value); }
        );
      }

      default: {
        throw new IllegalArgumentException('Invalid backing given: '.Objects::stringOf($type));
      }
    }
  }

  /**
   * Store encryption and decryption routines (unittest method only)
   *
   * @param  callable $encrypt
   * @param  callable $decrypt
   * @return void
   */
  public static function setBacking(\Closure $encrypt, \Closure $decrypt) {
    self::$encrypt= $encrypt;
    self::$decrypt= $decrypt;
  }

  /**
   * Constructor
   *
   * @param string $characters Characters to secure
   */
  public function __construct($characters) {
    $this->id= uniqid(microtime(true));
    $this->update($characters);
  }

  /**
   * Update with given characters
   *
   * @param  string $characters
   * @return void
   */
  protected function update(&$characters) {
    $key= $this->hashCode();
    try {
      self::$store[$key]= self::$encrypt->__invoke($characters);
    } catch (\Throwable $e) {
      // This intentionally catches *ALL* exceptions, in order not to fail
      // and produce a stacktrace (containing arguments on the stack that were)
      // supposed to be protected.
      // Also, cleanup XP error stack
      unset(self::$store[$key]);
      \xp::gc();
    }

    $characters= str_repeat('*', strlen($characters));
    $characters= null;
  }

  /**
   * Prevent serialization of object
   *
   * @return string[]
   */
  public function __sleep() {
    throw new IllegalStateException('Cannot serialize Secret instances.');
  }

  /** Reveal secured characters */
  public function reveal(): string {
    $key= $this->hashCode();
    if (!isset(self::$store[$key])) {
      throw new IllegalStateException('An error occurred during storing the encrypted secret.');
    }
    
    return self::$decrypt->__invoke(self::$store[$key]);
  }

  /**
   * Check whether given argument matches this secret
   *
   * @param  string|self $arg
   * @return bool
   */
  public function matches($arg): bool {
    if ($arg instanceof self) {
      return hash_equals($this->reveal(), $arg->reveal());
    } else {
      return hash_equals($this->reveal(), (string)$arg);
    }
  }

  /** Override string casts */
  public function __toString(): string { return $this->toString(); }

  /** Creates a hashcode */
  public function hashCode(): string { return $this->id; }

  /** Creates a string representation */
  public function toString(): string { return nameof($this).'('.$this->id.')'; }

  /**
   * Compares to another value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->id <=> $value->id : 1;
  }

  /** @return void */
  public function __destruct() {
    unset(self::$store[$this->hashCode()]);
  }
}
