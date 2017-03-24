<?php namespace util;

use lang\{Value, FormatException};

/**
 * Many URI schemes include a hierarchical element for a naming authority.
 * 
 * ```
 * authority   = [ userinfo "@" ] host [ ":" port ]
 * ```
 * 
 * @see   xp://util.URI#authority
 * @see   https://tools.ietf.org/html/rfc3986#section-3.2
 * @test  xp://net.xp_framework.unittest.util.AuthorityTest
 */
class Authority implements Value {
  public static $EMPTY;

  private $host, $port, $user, $password;

  static function __static() {
    self::$EMPTY= new self(null);
  }

  /**
   * Creates a new authority
   *
   * @param  string $host
   * @param  int $port
   * @param  string $user
   * @param  string|util.Secret $password
   */
  public function __construct($host, $port= null, $user= null, $password= null) {
    $this->host= $host;
    $this->port= $port;
    $this->user= $user;

    if (null === $password) {
      $this->password= null;
    } else if ($password instanceof Secret) {
      $this->password= $password;
    } else {
      $this->password= new Secret($password);
    }
  }

  /**
   * Parse a given input string 
   *
   * @param  string $input
   * @return self
   * @throws lang.FormatException
   */
  public static function parse($input) {
    if (!preg_match('!^(([^:@]+)(:([^@]+))?@)?([a-zA-Z0-9\.-]+|\[[^\]]+\])(:([0-9]+))?$!', $input, $matches)) {
      throw new FormatException('Cannot parse "'.$input.'": Authority malformed');
    }

    return new self(
      $matches[5],
      isset($matches[7]) ? (int)$matches[7] : null,
      $matches[2] === '' ? null : rawurldecode($matches[2]),
      $matches[4] === '' ? null : new Secret(rawurldecode($matches[4]))
    );
  }

  /** @return string */
  public function host() { return $this->host; }

  /** @return int */
  public function port() { return $this->port; }

  /** @return string */
  public function user() { return $this->user; }

  /** @return util.Secret */
  public function password() { return $this->password; }

  /**
   * Helper to create a string representations.
   *
   * @param  bool $reveal Whether to reveal password, if any
   * @return string
   */
  public function asString($reveal) {
    $s= '';
    isset($this->user) && $s.= (
      rawurlencode($this->user).
      (isset($this->password) ? ':'.($reveal ? rawurlencode($this->password->reveal()) : '********') : '').
      '@'
    );
    isset($this->host) && $s.= $this->host;
    isset($this->port) && $s.= ':'.$this->port;
    return $s;
  }

  /** @return string */
  public function __toString() {
    return $this->asString(true);
  }

  /** @return string */
  public function toString() {
    return nameof($this).'<'.$this->asString(false).'>';
  }

  /** @return string */
  public function hashCode() {
    return md5($this->asString(true));
  }

  /**
   * Compare to another value
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? $this->asString(true) <=> $value->asString(true) : 1;
  }
}