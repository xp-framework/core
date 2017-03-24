<?php namespace util;

use lang\IllegalStateException;

/**
 * Creates URI instances 
 *
 * @see   xp://util.URI#using
 * @see   xp://util.URI#with
 * @test  xp://net.xp_framework.unittest.util.URICreationTest
 */
class URICreation {
  public $scheme    = null;
  public $authority = null;
  public $path      = null;
  public $query     = null;
  public $fragment  = null;

  private $host      = null;
  private $port      = null;
  private $user      = null;
  private $password  = null;

  /**
   * Initialize creation instance
   *
   * @param  util.URI $uri Optional value to modify
   */
  public function __construct($uri= null) {
    if (null === $uri) return;

    $this->scheme= $uri->scheme();
    $this->authority= $uri->authority();
    $this->path= $uri->path();
    $this->query= $uri->query();
    $this->fragment= $uri->fragment();
  }

  /**
   * Sets scheme - mandatory!
   *
   * @param  string $value
   * @return self
   */
  public function scheme($value) { $this->scheme= $value; return $this; }

  /**
   * Sets authority (use NULL to remove)
   *
   * @param  string|util.Authority $value
   * @return self
   */
  public function authority($value) {
    if (null === $value) {
      $this->authority= null;
    } else if ($value instanceof Authority) {
      $this->authority= $value;
    } else {
      $this->authority= Authority::parse($value);
    }
    return $this;
  }

  /**
   * Sets host
   *
   * @param  string $value
   * @return self
   */
  public function host($value) { $this->host= $value; return $this; }

  /**
   * Sets port
   *
   * @param  string $value
   * @return self
   */
  public function port($value) { $this->port= $value; return $this; }

  /**
   * Sets user
   *
   * @param  string $value
   * @return self
   */
  public function user($value) { $this->user= $value; return $this; }

  /**
   * Sets password
   *
   * @param  string|util.Secret $value
   * @return self
   */
  public function password($value) {
    if (null === $value) {
      $this->password= null;
    } else if ($value instanceof Secret) {
      $this->password= $value;
    } else {
      $this->password= new Secret($value);
    }
    return $this;
  }

  /**
   * Sets path (use NULL to remove)
   *
   * @param  string $value
   * @return self
   */
  public function path($value) { $this->path= $value; return $this; }

  /**
   * Sets query (use NULL to remove)
   *
   * @param  string $value
   * @return self
   */
  public function query($value) { $this->query= $value; return $this; }

  /**
   * Sets fragment (use NULL to remove)
   *
   * @param  string $value
   * @return self
   */
  public function fragment($value) { $this->fragment= $value; return $this; }

  /**
   * Creates the URI
   *
   * @return util.URI
   * @throws lang.IllegalStateException
   */
  public function create() {

    // If host, port, user or password was set directly, merge
    if (null !== $this->host || null !== $this->port || null !== $this->user || null !== $this->password) {
      $this->authority= new Authority(
        $this->host ?? ($this->authority ? $this->authority->host() : null),
        $this->port ?? ($this->authority ? $this->authority->port() : null),
        $this->user ?? ($this->authority ? $this->authority->user() : null),
        $this->password ?? ($this->authority ? $this->authority->password() : null)
      );
    }

    // Sanity check
    if (null === $this->scheme) {
      throw new IllegalStateException('Cannot create URI without scheme');
    } else if (null === $this->authority && null === $this->path) {
      throw new IllegalStateException('Need either authority or path to create URI');
    }

    return new URI($this);
  }
}