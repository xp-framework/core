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
 * @test   xp://net.xp_framework.unittest.util.SodiumSecretTest
 * @test   xp://net.xp_framework.unittest.util.OpenSSLSecretTest
 * @test   xp://net.xp_framework.unittest.util.PlainTextSecretTest
 */
class Secret implements Value {
  const BACKING_SODIUM    = 0x01;
  const BACKING_OPENSSL   = 0x02;
  const BACKING_PLAINTEXT = 0x03;

  private $id;
  private static $store   = [];
  private static $encrypt = null;
  private static $decrypt = null;

  static function __static() {
    if (extension_loaded('sodium')) {
      self::useBacking(self::BACKING_SODIUM);
    } else if (extension_loaded('openssl')) {
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
      case self::BACKING_SODIUM: {
        if (!extension_loaded('sodium')) {
          throw new IllegalStateException('Backing "sodium" required but extension not available.');
        }
        $nonce= random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $key= sodium_crypto_secretbox_keygen();

        return self::setBacking(
          function($value) use ($key, $nonce) { return sodium_crypto_secretbox($value, $nonce, $key); },
          function($value) use ($key, $nonce) { return sodium_crypto_secretbox_open($value, $nonce, $key); }
        );
      }

      case self::BACKING_OPENSSL: {
        if (!extension_loaded('openssl')) {
          throw new IllegalStateException('Backing "openssl" required but extension not available.');
        }
        $key= md5(uniqid());
        $iv= openssl_random_pseudo_bytes(openssl_cipher_iv_length('des'));

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
    try {
      self::$store[$this->id]= self::$encrypt->__invoke($characters);
    } catch (\Throwable $e) {
      // This intentionally catches *ALL* exceptions, in order not to fail
      // and produce a stacktrace (containing arguments on the stack that were)
      // supposed to be protected.
      // Also, cleanup XP error stack
      unset(self::$store[$this->id]);
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
    if (!isset(self::$store[$this->id])) {
      throw new IllegalStateException('An error occurred during storing the encrypted secret.');
    }
    
    return self::$decrypt->__invoke(self::$store[$this->id]);
  }

  /**
   * Check whether given argument matches this secret
   *
   * @param  string|self $arg
   * @return bool
   */
  public function equals($arg): bool {
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
    unset(self::$store[$this->id]);
  }
}
