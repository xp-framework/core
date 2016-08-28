<?php namespace io;

use lang\IllegalStateException;

/**
 * Represents a Folder
 *
 * Usage:
 * ```
 * $d= new Folder('/etc/');
 * while (false !== ($entry= $d->getEntry())) {
 *   printf("%s/%s\n", $d->uri, $entry);
 * }
 * $d->close();
 * ```
 *
 * @test  xp://net.xp_framework.unittest.io.FolderTest
 */
class Folder extends \lang\Object {
  public 
    $uri      = '',
    $dirname  = '',
    $path     = '';
  
  private $_hdir = false;
    
  /**
   * Constructor
   *
   * @param   var base either a string or an io.Folder instance
   * @param   string... args components
   */
  public function __construct($base= null, ... $args) {
    if (null === $base) {
      return;
    } else if ($base instanceof self) {
      $composed= $base->getURI();
    } else {
      $composed= rtrim($base, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    }

    $this->setURI($composed.implode(DIRECTORY_SEPARATOR, $args));
  }
  
  /**
   * Destructor
   */
  public function __destruct() {
    $this->close();
  }
  
  /**
   * Close directory
   *
   * @return void
   */
  public function close() {
    if (false != $this->_hdir) $this->_hdir->close();
    $this->_hdir= false;
  }

  /**
   * Set URI
   *
   * @param   string uri the complete path name
   */
  public function setURI($uri) {

    // Add trailing / (or \, or whatever else DIRECTORY_SEPARATOR is defined to) if necessary
    $uri= rtrim(str_replace('/', DIRECTORY_SEPARATOR, $uri), DIRECTORY_SEPARATOR);

    // Calculate absolute path. Use own implementation as realpath returns FALSE in some
    // implementations if the underlying directory does not exist.
    $components= explode(DIRECTORY_SEPARATOR, $uri);
    $i= 1;
    if ('' === $components[0]) {
      $this->uri= rtrim(realpath(DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    } else if ((strncasecmp(PHP_OS, 'Win', 3) === 0) && strlen($components[0]) > 1 && ':' === $components[0]{1}) {
      $this->uri= rtrim(realpath($components[0]), DIRECTORY_SEPARATOR);
    } else if ('..' === $components[0]) {
      $this->uri= rtrim(realpath('..'), DIRECTORY_SEPARATOR);
    } else {
      $this->uri= rtrim(realpath('.'), DIRECTORY_SEPARATOR);
      $i= ('.' === $components[0]) ? 1 : 0;
    }
    for ($s= sizeof($components); $i < $s; $i++) {
      if ('.' === $components[$i]) {
        continue;
      } else if ('..' === $components[$i]) {
        if ($p= strrpos($this->uri, DIRECTORY_SEPARATOR)) {
          $this->uri= substr($this->uri, 0, $p);
        }
      } else {
        $this->uri.= DIRECTORY_SEPARATOR.$components[$i];
        if (is_link($this->uri)) {
          $this->uri= readlink($this->uri);
        }
      }
    }
    
    // Calculate path and name
    $this->uri.= DIRECTORY_SEPARATOR;
    $this->path= dirname($this->uri);
    $this->dirname= basename($this->uri);
  }
  
  /** Get URI */
  public function getURI(): string { return $this->uri; }
  
  /**
   * Create this directory, recursively, if needed.
   *
   * @param   int permissions default 0700
   * @return  bool TRUE in case the creation succeeded or the directory already exists
   * @throws  io.IOException in case of an error
   */
  public function create($permissions= 0700) {
    if ('' == (string)$this->uri) {
      throw new IOException('Cannot create folder with empty name');
    }
    
    // Border-case: Folder already exists
    if (is_dir($this->uri)) return true;

    $i= 0;
    $umask= umask(000);
    while (false !== ($i= strpos($this->uri, DIRECTORY_SEPARATOR, $i))) {
      if (is_dir($d= substr($this->uri, 0, ++$i))) continue;
      if (false === mkdir($d, $permissions)) {
        umask($umask);
        throw new IOException(sprintf('mkdir("%s", %d) failed', $d, $permissions));
      }
    }
    umask($umask);
    
    return true;
  }
  
  /**
   * Delete this folder and all its subentries recursively
   * Warning: Stops at the first element that can't be deleted!
   *
   * @return  bool success
   * @throws  io.IOException in case one of the entries could'nt be deleted
   */
  public function unlink($uri= null) {
    if (null === $uri) $uri= $this->uri; // We also use this recursively
    
    if (false === ($d= dir($uri))) {
      throw new IOException('Directory '.$uri.' does not exist');
    }
    
    while (false !== ($e= $d->read())) {
      if ('.' == $e || '..' == $e) continue;
      
      $fn= $d->path.$e;
      if (!is_dir($fn)) {
        $ret= unlink($fn);
      } else {
        $ret= $this->unlink($fn.DIRECTORY_SEPARATOR);
      }
      if (false === $ret) throw new IOException(sprintf('unlink of "%s" failed', $fn));
    }
    $d->close();

    if (false === rmdir($uri)) throw new IOException(sprintf('unlink of "%s" failed', $uri));
    
    return true;
  }

  /**
   * Move this directory
   *
   * Warning: Open directories cannot be moved. Use the close() method to
   * close the directory first
   *
   * @return  bool success
   * @throws  io.IOException in case of an error (e.g., lack of permissions)
   * @throws  lang.IllegalStateException in case the directory is still open
   */
  public function move($target) {
    if (is_resource($this->_hdir)) {
      throw new IllegalStateException('Directory still open');
    }
    if (false === rename($this->uri, $target)) {
      throw new IOException('Cannot move directory '.$this->uri.' to '.$target);
    }
    return true;
  }

  /** Returns whether this directory exists */
  public function exists(): bool { return is_dir($this->uri); }

  /** Returns entries in this folder */
  public function entries(): FolderEntries { return new FolderEntries($this); }

  /**
   * Retrieve when the folder was created
   *
   * @return  int The date the file was created as a unix-timestamp
   * @throws  io.IOException in case of an error
   */
  public function createdAt() {
    if (false === ($mtime= filectime($this->uri))) {
      throw new IOException('Cannot get mtime for '.$this->uri);
    }
    return $mtime;
  }

  /**
   * Retrieve last access time
   *
   * Note: 
   * The atime of a file is supposed to change whenever the data blocks of a file 
   * are being read. This can be costly performancewise when an application 
   * regularly accesses a very large number of files or directories. Some Unix 
   * filesystems can be mounted with atime updates disabled to increase the 
   * performance of such applications; USENET news spools are a common example. 
   * On such filesystems this function will be useless. 
   *
   * @return  int The date the file was last accessed as a unix-timestamp
   * @throws  io.IOException in case of an error
   */
  public function lastAccessed() {
    if (false === ($atime= fileatime($this->uri))) {
      throw new IOException('Cannot get atime for '.$this->uri);
    }
    return $atime;
  }
  
  /**
   * Retrieve last modification time
   *
   * @return  int The date the file was last modified as a unix-timestamp
   * @throws  io.IOException in case of an error
   */
  public function lastModified() {
    if (false === ($mtime= filemtime($this->uri))) {
      throw new IOException('Cannot get mtime for '.$this->uri);
    }
    return $mtime;
  }

  /** Returns whether a given value is equal to this folder */
  public function equals($cmp): bool {
    return $cmp instanceof self && $cmp->hashCode() === $this->hashCode();
  }

  /** Returns a hashcode */
  public function hashCode(): string { return md5($this->uri); }

  /** Returns a string representation of this object */
  public function toString(): string {
    return sprintf('%s(uri= %s)', nameof($this), $this->uri);
  }
}
