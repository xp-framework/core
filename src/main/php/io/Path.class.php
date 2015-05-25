<?php namespace io;

use lang\IllegalStateException;
use lang\IllegalArgumentException;
use io\collections\IOElement;

class Path extends \lang\Object {
  protected $path;

  /**
   * Creates a path from a given input
   *
   * @param  var[] $input
   * @return string
   */
  protected function pathFor($input) {
    if (empty($input)) {
      return '';
    } else if ($input[0] instanceof Folder) {
      $components= [substr(array_shift($input)->getURI(), 0, -1)];
    } else if ($input[0] instanceof File || $input[0] instanceof IOElement) {
      $components= [array_shift($input)->getURI()];
    } else {
      $components= [];
    }

    foreach ($input as $arg) {
      $components[]= strtr((string)$arg, ['/' => DIRECTORY_SEPARATOR]);
    }
    return implode(DIRECTORY_SEPARATOR, $components);
  }

  /**
   * Creates a new instance with a variable number of arguments
   *
   * @param  var $base Either a string, a Path, a File, Folder or IOElement
   * @param  var... $args Further components to be concatenated, Paths or strings.
   */
  public function __construct($base) {
    if (is_array($base)) {
      $this->path= $this->pathFor($base);
    } else {
      $this->path= $this->pathFor(func_get_args());
    }
  }

  /**
   * Creates a new instance from an array of objects
   *
   * @param  var[] $args
   * @return self
   */
  public static function compose(array $args) {
    return new self($args);
  }

  /**
   * Returns the name of this path
   *
   * @return string
   */
  public function name() {
    return basename($this->path);
  }

  /**
   * Returns the parent of this path
   *
   * @return self
   */
  public function parent() {
    if ('' === $this->path) {
      return new self('..');
    } else if (0 === strncmp($this->path, '..', 2)) {
      return new self('..', $this->path);
    }

    $parent= dirname($this->path);
    if ($parent === $this->path) {
      return null;
    } else {
      return new self($parent);
    }
  }

  /**
   * Tests whether this path exists
   *
   * @return bool
   */
  public function exists() {
    return file_exists($this->path);
  }

  /**
   * Tests whether this path exists and references a file
   *
   * @return bool
   */
  public function isFile() {
    return is_file($this->path);
  }

  /**
   * Tests whether this path exists and references a folder
   *
   * @return bool
   */
  public function isFolder() {
    return is_dir($this->path);
  }

  /**
   * Realpath
   *
   * @see    php://realpath
   * @param  string $path
   * @param  string $wd
   * @return string
   */
  protected static function real($path, $wd) {
    if (DIRECTORY_SEPARATOR === $path{0}) {
      $normalized= '';
      $components= explode(DIRECTORY_SEPARATOR, substr($path, 1));
    } else if (2 === sscanf($path, '%c%*[:]', $drive)) {
      $normalized= $drive.':';
      $components= explode(DIRECTORY_SEPARATOR, substr($path, 3));
    } else if (null === $wd) {
      throw new IllegalStateException('Cannot resolve '.$path);
    } else {
      return self::real($wd.DIRECTORY_SEPARATOR.$path, null);
    }

    $check= true;
    foreach ($components as $component) {
      if ('' === $component || '.' === $component) {
        // Skip
      } else if ('..' === $component) {
        $normalized= substr($normalized, 0, strrpos($normalized, DIRECTORY_SEPARATOR));
        $check= true;
      } else {
        $normalized.= DIRECTORY_SEPARATOR.$component;
        if ($check) {
          $stat= @lstat($normalized);
          if (false === $stat) {
            $check= false;
          } else if (0120000 === ($stat[2] & 0120000)) {
            $normalized= readlink($normalized);
          }
        }
      }
    }
    return $normalized;
  }

  /**
   * Returns the real URI for this path, resolving links if necessary.
   * If no working directory is given, the current working directory is 
   * used to resolve relative paths.
   *
   * @see    php://getcwd
   * @param  string $wd Working directory
   * @return string
   */
  public function asURI($wd= null) {
    return self::real($this->path, $wd ?: getcwd());
  }

  /**
   * Returns the real path for this path, resolving links if necessary.
   * If no working directory is given, the current working directory is 
   * used to resolve relative paths.
   *
   * @see    php://getcwd
   * @param  string $wd Working directory
   * @return self
   */
  public function asRealpath($wd= null) {
    return new self(self::real($this->path, $wd ?: getcwd()));
  }

  /**
   * Returns a file instance for this path
   *
   * @return io.File
   * @throws lang.IllegalStateException if the path is not a file
   */
  public function asFile() {
    if (is_file($this->path)) return new File($this->path);
    throw new IllegalStateException($this->path.' is not a file');
  }

  /**
   * Returns a folder instance for this path
   *
   * @return io.File
   * @throws lang.IllegalStateException if the path is not a folder
   */
  public function asFolder() {
    if (is_dir($this->path)) return new Folder($this->path);
    throw new IllegalStateException($this->path.' is not a folder');
  }

  /**
   * Tests whether this path is absolute, e.g. `/usr` or `C:\Windows`.
   *
   * @return bool
   */
  public function isAbsolute() {
    return '' !== $this->path && (
      DIRECTORY_SEPARATOR === $this->path{0} ||
      2 === sscanf($this->path, '%c%[:]', $drive, $colon)
    );
  }

  /**
   * Normalizes path sections. Note: This method does not access the filesystem,
   * it only removes redundant elements.
   *
   * @return self
   */
  public function normalize() {
    $components= explode(DIRECTORY_SEPARATOR, $this->path);
    $normalized= [];
    foreach ($components as $component) {
      if ('' === $component || '.' === $component) {
        // Skip
      } else if ('..' === $component && !empty($normalized)) {
        $last= array_pop($normalized);
        if ('..' === $last) {
          $normalized[]= '..';
          $normalized[]= '..';
        }
       } else {
        $normalized[]= $component;
      }
    }
    return self::compose($normalized);
  }

  /**
   * Resolves given path against this path
   *
   * ```php
   * $r= (new Path('/usr/local'))->resolve('bin');    // "/usr/local/bin"
   * $r= (new Path('/usr/local'))->resolve('/usr');   // "../.."
   * ```
   *
   * @param  var $other Either a string or a path
   * @return self
   */
  public function resolve($arg) {
    $other= $arg instanceof self ? $arg : new self($arg);

    if ($other->isAbsolute()) {
      return $other->relativeTo($this);
    } else {
      return self::compose([$this->path, $other->path]);
    }
  }

  /**
   * Returns how many parents are necessary to come from a to b
   *
   * @param  string $a
   * @param  string $b
   * @param  int
   */
  protected function parents($a, $b) {
    for ($r= 0; $a !== $b; $r++) {
      $a= dirname($a);
      $b= dirname($b);
    }
    return $r;
  }

  /**
   * Creates relative path
   *
   * @param  var $other Either a string or a path
   * @return self
   */
  public function relativeTo($arg) {
    $other= $arg instanceof self ? $arg : new self($arg);
    if ($this->isAbsolute() !== $other->isAbsolute()) {
      throw new IllegalArgumentException('Cannot calculate relative path from '.$this.' to '.$other);
    }

    $a= $this->normalize();
    $b= $other->normalize();

    if ($a->path === $b->path) {
      return new self('');
    } else if ('' === $b->path) {
      return $a;
    } else if (0 === substr_compare($a->path, $b->path, 0, $bl= strlen($b->path))) {
      return new self(substr($a->path, $bl + strlen(DIRECTORY_SEPARATOR)));
    } else if (0 === substr_compare($a->path, $b->path, 0, $al= strlen($a->path))) {
      return self::compose(array_fill(0, substr_count($b->path, DIRECTORY_SEPARATOR, $al), '..'));
    } else {
      $parents= $this->parents($a->path, $b->path);
      return self::compose(array_merge(
        array_fill(0, $parents, '..'),
        array_slice(explode(DIRECTORY_SEPARATOR, $a->path), -$parents))
      );
    }
  }

  /**
   * Creates a string representation of this path. Uses system's directory
   * separator per default but this can be overridden by passing one.
   *
   * @param  string $separator
   * @return string
   */
  public function toString($separator= DIRECTORY_SEPARATOR) {
    return strtr($this->path, [DIRECTORY_SEPARATOR => $separator]);
  }

  /**
   * Returns whether this path instance is equal to a given object.
   *
   * @param  var $cmp
   * @return bool
   */
  public function equals($cmp) {
    return $cmp instanceof self && $this->normalize()->path === $cmp->normalize()->path;
  }

  /** @return string */
  public function __toString() { return $this->path; }
}