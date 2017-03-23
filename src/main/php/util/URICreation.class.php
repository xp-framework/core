<?php namespace util;

use lang\IllegalStateException;

class URICreation {
  public $scheme    = null;
  public $authority = null;
  public $path      = null;
  public $query     = null;
  public $fragment  = null;

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
    if (null === $this->scheme) {
      throw new IllegalStateException('Cannot create URI without scheme');
    } else if (null === $this->authority && null === $this->path) {
      throw new IllegalStateException('Need either authority or path to create URI');
    }

    return new URI($this);
  }
}