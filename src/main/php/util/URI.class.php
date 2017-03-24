<?php namespace util;

use lang\{Value, FormatException};

/**
 * A Uniform Resource Identifier (URI) is a compact sequence of
 * characters that identifies an abstract or physical resource.
 *
 * ```
 *   foo://example.com:8042/over/there?name=ferret#nose
 *   \_/   \______________/\_________/ \_________/ \__/
 *    |           |            |            |        |
 * scheme     authority       path        query   fragment
 *    |   _____________________|__
 *   / \ /                        \
 *   urn:example:animal:ferret:nose
 * ```
 *
 * @see   https://tools.ietf.org/html/rfc3986
 * @test  xp://net.xp_framework.unittest.util.URITest
 */
class URI implements Value {
  private $scheme, $authority, $path, $query, $fragment;

  /**
   * Creates a URI instance, either by parsing a string or by using a
   * given `URICreation` instance, which offers a fluent interface.
   *
   * @see    https://tools.ietf.org/html/rfc3986#section-1.1.2
   * @param  string|util.URICreation $arg
   * @throws lang.FormatException if string argument cannot be parsed
   */
  public function __construct($arg) {
    if ($arg instanceof URICreation) {
      $this->scheme= $arg->scheme;
      $this->authority= $arg->authority;
      $this->path= $arg->path;
      $this->query= $arg->query;
      $this->fragment= $arg->fragment;
    } else if (preg_match('!^([a-zA-Z][a-zA-Z0-9\+]*):(//([^/?#]*)(/[^?#]*)?|([^?#]+))(\?[^#]*)?(#.*)?!', $arg, $matches)) {
      $this->scheme= $matches[1];

      if (isset($matches[5]) && '' !== $matches[5]) {   // E.g. mailto:test@example.com
        $this->authority= null;
        $this->path= $matches[5];
      } else if ('' === $matches[3]) {                  // E.g. file:///usr/local/etc/php.ini
        $this->authority= Authority::$EMPTY;
        $this->path= $matches[4];
      } else {                                          // E.g. http://example.com/
        $this->authority= Authority::parse($matches[3]);
        $this->path= $matches[4] ?? null;
      }

      $this->query= isset($matches[6]) && '' !== $matches[6] ? substr($matches[6], 1) : null;
      $this->fragment= isset($matches[7]) && '' !== $matches[7] ? substr($matches[7], 1) : null;
    } else {
      throw new FormatException('Cannot parse "'.$arg.'"');
    }
  }

  /**
   * Use a fluent interface to create a URI
   *
   * ```php
   * $uri= URI::with()
   *   ->scheme('http')
   *   ->authority('example.com')
   *   ->path('/index')
   *   ->create();
   * ;
   * ```
   */
  public static function with(): URICreation { return new URICreation(); }

  /**
   * Use a fluent interface to create a new URI based on this URI
   *
   * ```php
   * $uri= (new URI('http://example.com'))->using()->scheme('https')->create();
   * ```
   */
  public function using(): URICreation { return new URICreation($this); }

  /** @return bool */
  public function isOpaque() { return null === $this->authority; }

  /** @return string */
  public function scheme() { return $this->scheme; }

  /** @return util.Authority */
  public function authority($default= null) { return $this->authority ?? $default; }

  /** @return string */
  public function host() { return $this->authority ? $this->authority->host() : null; }

  /** @return int */
  public function port() { return $this->authority ? $this->authority->port() : null; }

  /** @return string */
  public function user() { return $this->authority ? $this->authority->user() : null; }

  /** @return util.Secret */
  public function password() { return $this->authority ? $this->authority->password() : null; }

  /** @return string */
  public function path($default= null) { return $this->path ?? $default; }

  /** @return string */
  public function query($default= null) { return $this->query ?? $default; }

  /** @return string */
  public function fragment($default= null) { return $this->fragment ?? $default; }

  /** @return self */
  public function canonicalize() { return (new URICanonicalization())->canonicalize($this); }

  /**
   * Helper to create a string representations.
   *
   * @see    https://tools.ietf.org/html/rfc3986#section-5.3
   * @param  bool $reveal Whether to reveal password, if any
   * @return string
   */
  public function asString($reveal) {
    $s= $this->scheme.':';
    isset($this->authority) && $s.= '//'.$this->authority->asString($reveal);
    isset($this->path) && $s.= $this->path;
    isset($this->query) && $s.= '?'.$this->query;
    isset($this->fragment) && $s.= '#'.$this->fragment;
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