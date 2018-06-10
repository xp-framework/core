<?php namespace io;

use io\collections\IOElement;
use lang\IllegalArgumentException;
use lang\IllegalStateException;
use lang\Value;

/**
 * Represents a file system path
 *
 * @see  https://blogs.msdn.microsoft.com/jeremykuhne/2016/04/21/path-normalization/
 * @test xp://net.xp_framework.unittest.io.PathTest
 */
class Path implements Value {
  const EXISTING = true;

  protected $path;

  /**
   * Creates a path from a given input
   *
   * @param  var[] $input
   * @return string
   */
  private static function pathFor($input) {
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
  public function __construct($base, ... $args) {
    if (is_array($base)) {
      $this->path= self::pathFor($base);
    } else {
      array_unshift($args, $base);
      $this->path= self::pathFor($args);
    }
  }

  /**
   * Creates a new instance from an array of objects
   *
   * @param  var[] $args
   * @return self
   */
  public static function compose(array $args): self {
    return new self($args);
  }

  /**
   * Creates a new instance with a variable number of arguments
   *
   * @see    php://realpath
   * @param  var $arg Either a string, a Path, a File, Folder or IOElement or an array
   * @param  var $wd Working directory A string, a Path, Folder or IOElement
   * @return self
   */
  public static function real($arg, $wd= null): self {
    if (is_array($arg)) {
      $path= self::pathFor($arg);
    } else {
      $path= self::pathFor([$arg]);
    }
    return new self(self::real0($path, $wd ?: getcwd()));
  }

  /** Returns the name of this path */
  public function name(): string { return basename($this->path); }

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

  /** Tests whether this path exists */
  public function exists(): bool { return file_exists($this->path); }

  /** Tests whether this path exists and references a file */
  public function isFile(): bool { return is_file($this->path); }

  /** Tests whether this path exists and references a folder */
  public function isFolder(): bool { return is_dir($this->path); }

  /** Tests whether this path references an empty string */
  public function isEmpty() { return '' === $this->path; }

  /** Tests whether this path is absolute, e.g. `/usr` or `C:\Windows` */
  public function isAbsolute(): bool {
    return '' !== $this->path && (
      DIRECTORY_SEPARATOR === $this->path{0} ||
      2 === sscanf($this->path, '%c%[:]', $drive, $colon)
    );
  }

  /**
   * Normalization - like realpath, but doesn't check filesystem.
   *
   * @param  string $path
   * @return string
   */
  private static function norm0($path) {
    $l= strlen($path);
    if (0 === $l) {
      return '';
    } else if (DIRECTORY_SEPARATOR === $path{0}) {
      $components= explode(DIRECTORY_SEPARATOR, substr($path, 1));
      $base= DIRECTORY_SEPARATOR;
    } else if ($l > 1 && ':' === $path{1}) {
      $components= explode(DIRECTORY_SEPARATOR, substr($path, 3));
      $base= strtoupper($path{0}).':'.DIRECTORY_SEPARATOR;
    } else {
      $components= explode(DIRECTORY_SEPARATOR, $path);
      $base= null;
    }

    $normalized= [];
    foreach ($components as $component) {
      if ('' === $component || '.' === $component) {
        // Skip
      } else if ('..' === $component) {
        $last= array_pop($normalized);
        if (null === $base) {
          if (null === $last) {
            $normalized[]= '..';
          } else if ('..' === $last) {
            $normalized[]= '..';
            $normalized[]= '..';
          }
        }
      } else {
        $normalized[]= $component;
      }
    }

    if ($normalized) {
      return $base.implode(DIRECTORY_SEPARATOR, $normalized);
    } else {
      return $base ?: '.';
    }
  }

  /**
   * Realpath
   *
   * @see    php://realpath
   * @param  string $path
   * @param  var $wd
   * @return string
   */
  private static function real0($path, $wd) {
    if (DIRECTORY_SEPARATOR === $path{0}) {
      $normalized= '';
      $components= explode(DIRECTORY_SEPARATOR, substr($path, 1));
    } else if (2 === sscanf($path, '%c%*[:]', $drive)) {
      $normalized= strtoupper($drive).':';
      $components= explode(DIRECTORY_SEPARATOR, substr($path, 3));
    } else if (null === $wd) {
      throw new IllegalStateException('Cannot resolve '.$path);
    } else {
      return self::real0(self::pathFor([$wd]).DIRECTORY_SEPARATOR.$path, null);
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
   * @param  var $wd Working directory A string, a Path, Folder or IOElement
   * @return string
   */
  public function asURI($wd= null): string {
    return self::real0($this->path, $wd ?: getcwd());
  }

  /**
   * Returns the real path for this path, resolving links if necessary.
   * If no working directory is given, the current working directory is 
   * used to resolve relative paths.
   *
   * @see    php://getcwd
   * @param  var $wd Working directory A string, a Path, Folder or IOElement
   * @return self
   */
  public function asRealpath($wd= null): self {
    return new self(self::real0($this->path, $wd ?: getcwd()));
  }

  /**
   * Returns a file instance for this path
   *
   * @param  bool $existing Whether only to return existing files
   * @return io.File
   * @throws lang.IllegalStateException if the path is not a file
   */
  public function asFile(bool $existing= false): File {
    if (is_file($this->path)) {
      return new File($this->path);
    } else if (file_exists($this->path)) {
      throw new IllegalStateException($this->path.' exists but is not a file');
    } else if ($existing) {
      throw new IllegalStateException($this->path.' does not exist');
    } else {
      return new File($this->path);
    }
  }

  /**
   * Returns a folder instance for this path
   *
   * @param  bool $existing Whether only to return existing folder
   * @return io.Folder
   * @throws lang.IllegalStateException if the path is not a folder
   */
  public function asFolder(bool $existing= false): Folder {
    if (is_dir($this->path)) {
      return new Folder($this->path);
    } else if (file_exists($this->path)) {
      throw new IllegalStateException($this->path.' exists but is not a folder');
    } else if ($existing) {
      throw new IllegalStateException($this->path.' does not exist');
    } else {
      return new Folder($this->path);
    }
  }

  /**
   * Normalizes path sections. Note: This method does not access the filesystem,
   * it only removes redundant elements.
   */
  public function normalize(): self {
    return new self(self::norm0($this->path));
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
  public function resolve($arg): self {
    $other= $arg instanceof self ? $arg : new self($arg);

    if ($other->isAbsolute()) {
      return $other->relativeTo($this);
    } else {
      return self::compose([$this->path, $other->path]);
    }
  }

  /**
   * Creates relative path
   *
   * @param  var $other Either a string or a path
   * @return self
   */
  public function relativeTo($arg): self {
    $other= $arg instanceof self ? $arg : new self($arg);
    if ($this->isAbsolute() !== $other->isAbsolute()) {
      throw new IllegalArgumentException('Cannot calculate relative path from '.$this.' to '.$other);
    }

    $a= rtrim(self::norm0($this->path), DIRECTORY_SEPARATOR);
    $b= rtrim(self::norm0($other->path), DIRECTORY_SEPARATOR);

    if ($a === $b) {
      return new self('');
    } else if ('.' === $b) {
      return new self($a);
    } else if ('' === $b) {
      return new self(ltrim($a, DIRECTORY_SEPARATOR));
    } else {
      $pa= explode(DIRECTORY_SEPARATOR, $a);
      $pb= explode(DIRECTORY_SEPARATOR, $b);
      $s= sizeof($pb);
      for ($i= 0; $i < min(sizeof($pa), $s) && $pa[$i] === $pb[$i]; $i++) { }
      return new self(array_merge(array_fill(0, $s - $i, '..'), array_slice($pa, $i)));
    }
  }

  /**
   * Creates a string representation of this path. Uses system's directory
   * separator per default but this can be overridden by passing one.
   *
   * @param  string $separator
   * @return string
   */
  public function toString(string $separator= DIRECTORY_SEPARATOR): string {
    return strtr($this->path, [DIRECTORY_SEPARATOR => $separator]);
  }

  /** Returns a hashcode for this path instance */
  public function hashCode(): string { return $this->normalize()->path; }

  /** Returns whether this path instance is equal to a given object */
  public function compareTo($value): int {
    return $value instanceof self ? strcmp($this->normalize()->path, $value->normalize()->path) : 1;
  }

  /** String casts */
  public function __toString(): string { return $this->path; }
}