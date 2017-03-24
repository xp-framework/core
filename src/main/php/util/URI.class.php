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
 * This class implements a URI reference, which is either a
 * URI or a relative reference.
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
   * @param  string|util.URICreation $base
   * @param  string $relative Optional relative URI
   * @throws lang.FormatException if string argument cannot be parsed
   */
  public function __construct($base, $relative= null) {
    if ($base instanceof URICreation) {
      $this->scheme= $base->scheme;
      $this->authority= $base->authority;
      $this->path= $base->path;
      $this->query= $base->query;
      $this->fragment= $base->fragment;
    } else if (preg_match('/^([a-zA-Z][a-zA-Z0-9\+\-\.]*):(.+)/', $base, $matches)) {
      $this->scheme= $matches[1];
      list($this->authority, $this->path, $this->query, $this->fragment)= $this->parse($matches[2]);
    } else {
      $this->scheme= null;
      list($this->authority, $this->path, $this->query, $this->fragment)= $this->parse($base);
    }

    if (null !== $relative) {
      $this->resolve0(...$this->parse($relative));
    }
  }

  /**
   * Parse relative URI into authority, path, query and fragment
   *
   * @param  string $relative
   * @return var[]
   */
  private function parse($relative) {
    if (0 === strlen($relative)) {
      throw new FormatException('Cannot parse empty input');
    }

    preg_match('!^((//)([^/?#]*)(/[^?#]*)?|([^?#]*))(\?[^#]*)?(#.*)?!', $relative, $matches);
    if ('//' === $matches[2]) {
      $authority= '' === $matches[3] ? Authority::$EMPTY : Authority::parse($matches[3]);
      $path= isset($matches[4]) && '' !== $matches[4] ? $matches[4] : null;
    } else {
      $authority= null;
      $path= '' === $matches[5] ? null : $matches[5];
    }

    $query= isset($matches[6]) && '' !== $matches[6] ? substr($matches[6], 1) : null;
    $fragment= isset($matches[7]) && '' !== $matches[7] ? substr($matches[7], 1) : null;

    return [$authority, $path, $query, $fragment];
  }

  /**
   * Resolve authority, path, query and fragment against this URI
   *
   * @see    https://tools.ietf.org/html/rfc3986#section-5.2.2
   * @param  util.Authority $authority
   * @param  string $path
   * @param  string $query
   * @param  string $fragment
   * @return void
   */
  private function resolve0($authority, $path, $query, $fragment) {
    if ($authority) {
      $this->authority= $authority;
      $this->path= $path;
    } else if (null === $path) {
      if (null === $query) $query= $this->query;
    } else if ('/' === $path{0}) {
      $this->path= $path;
    } else if (null === $this->path) {
      $this->path= '/'.$path;
    } else if ('/' === $this->path{strlen($this->path)- 1}) {
      $this->path= $this->path.$path;
    } else {
      $this->path= substr($this->path, 0, strpos($this->path, '/')).'/'.$path;
    }

    $this->query= $query;
    $this->fragment= $fragment;
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
  public function isRelative() { return null === $this->scheme; }

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
   * Resolves another URI against this URI. If the given URI is absolute,
   * it's returned directly. Otherwise, a new URI is returned; with the
   * given URI's authority (if any), its path relativized against this URI's
   * path, and its query and fragment.
   *
   * @see    https://tools.ietf.org/html/rfc3986#section-5
   * @param  string|self $arg
   * @return self
   */
  public function resolve($arg) {
    $uri= $arg instanceof self ? $arg : new self($arg);

    if ($uri->scheme) {
      return $uri;
    } else {
      $result= clone $this;
      $result->resolve0($uri->authority, $uri->path, $uri->query, $uri->fragment);
      return $result;
    }
  }

  /**
   * Helper to create a string representations.
   *
   * @see    https://tools.ietf.org/html/rfc3986#section-5.3
   * @param  bool $reveal Whether to reveal password, if any
   * @return string
   */
  public function asString($reveal) {
    $s= '';
    isset($this->scheme) && $s.= $this->scheme.':';
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