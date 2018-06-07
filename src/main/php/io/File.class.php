<?php namespace io;

use io\streams\{InputStream, FileInputStream, OutputStream, FileOutputStream};
use lang\{IllegalArgumentException, IllegalStateException, Value};

/**
 * Instances of the file class serve as an opaque handle to the underlying machine-
 * specific structure representing an open file.
 * 
 * @test xp://net.xp_framework.unittest.io.FileTest
 * @test xp://net.xp_framework.unittest.io.FileIntegrationTest
 */
class File implements Channel, Value {
  const READ =      'rb';          // Read
  const READWRITE = 'rb+';         // Read/Write
  const WRITE =     'wb';          // Write
  const REWRITE =   'wb+';         // Read/Write, truncate on open
  const APPEND =    'ab';          // Append (Read-only)
  const READAPPEND ='ab+';         // Append (Read/Write)

  public 
    $uri=         '', 
    $filename=    '',
    $path=        '',
    $extension=   '',
    $mode=        self::READ;
  
  protected $_fd= null;
  
  /**
   * Constructor. Supports creation via one of the following ways:
   *
   * ```php
   * // via resource
   * $f= new File(fopen('lang/Type.class.php', File::READ));
   *
   * // via filename
   * $f= new File('lang/Type.class.php');
   *  
   * // via dir- and filename
   * $f= new File('lang', 'Type.class.php');
   *
   * // via folder and filename
   * $f= new File(new Folder('lang'), 'Type.class.php');
   * ```
   *
   * @param   var $base either a resource or a filename
   * @param   string $uri
   */
  public function __construct($base, $uri= null) {
    if (is_resource($base)) {
      $this->uri= null;
      $this->_fd= $base;
    } else if (null === $uri) {
      $this->setURI($base);
    } else if ($base instanceof Folder) {
      $this->setURI($base->getURI().$uri);
    } else {
      $this->setURI(rtrim($base, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$uri);
    }
  }

  /** @return io.streams.InputStream */
  public function in(): InputStream { return new FileInputStream($this); }

  /** @return io.streams.OutputStream */
  public function out(): OutputStream { return new FileOutputStream($this); }

  /** Retrieve internal file handle */
  public function getHandle() { return $this->_fd; }
  
  /**
   * Returns the URI of the file 
   *
   * @return string or NULL
   */
  public function getURI() {
    return $this->uri;
  }
  
  /** Returns the filename of the file */
  public function getFileName(): string { return $this->filename; }

  /** Get Path */
  public function getPath(): string { return $this->path; }

  /**
   * Get Extension
   *
   * @return string or NULL if there is no extension
   */
  public function getExtension() { return $this->extension; }

  /**
   * Set this file's URI
   *
   * @param   string uri
   * @throws  lang.IllegalArgumentException in case an invalid file name was given
   */
  public function setURI($uri) {
    static $allowed= ['xar://*', 'php://stderr', 'php://stdout', 'php://stdin', 'res://*'];

    // Check validity, handle scheme and non-scheme URIs
    $uri= (string)$uri;
    if (0 === strlen($uri) || false !== strpos($uri, "\0")) {
      throw new IllegalArgumentException('Invalid filename "'.addcslashes($uri, "\0..\17").'"');
    } else if (false !== ($s= strpos($uri, '://'))) {
      if (!in_array($uri, $allowed) && !in_array(substr($uri, 0, 6).'*', $allowed)) {
        throw new IllegalArgumentException('Invalid scheme URI "'.$uri.'"');
      }
      $this->uri= $uri;
      $p= max($s+ 3, false === ($pq= strpos($uri, '?', $s)) ? -1 : $pq+ 1);
    } else {
      $this->uri= realpath($uri);
      if ('' == $this->uri && $uri != $this->uri) $this->uri= $uri;
      $p= 0;
    }

    // Split into components. Always allow forward slashes!
    $p= max(
      $p,
      false === ($ps= strrpos($uri, '/', $s)) ? -1 : $ps,
      false === ($pd= strrpos($uri, DIRECTORY_SEPARATOR, $s)) ? -1 : $pd
    );
    $this->path= substr($uri, 0, $p);
    $this->filename= ltrim(substr($uri, $p), '/'.DIRECTORY_SEPARATOR);
    $this->extension= (false === ($pe= strrpos($this->filename, '.', $s))) ? null : substr($this->filename, $pe+ 1);
  }

  /**
   * Open the file
   *
   * @param   string mode one of the File::* constants
   * @return  self
   * @throws  io.FileNotFoundException in case the file is not found
   * @throws  io.IOException in case the file cannot be opened (e.g., lacking permissions)
   */
  public function open($mode= self::READ): self {
    $this->mode= $mode;
    if (
      self::READ === $mode && 
      0 !== strncmp('php://', $this->uri, 6) &&
      !$this->exists()
    ) throw new FileNotFoundException('File "'.$this->uri.'" not found');
    
    $this->_fd= fopen($this->uri, $this->mode);
    if (!$this->_fd) {
      $e= new IOException('Cannot open '.$this->uri.' mode '.$this->mode);
      \xp::gc(__FILE__);
      throw $e;
    }
          
    return $this;
  }
  
  /** Returns whether this file is open */
  public function isOpen(): bool { return is_resource($this->_fd); }
  
  /** Returns whether this file eixts */
  public function exists(): bool { return file_exists($this->uri); }
  
  /**
   * Retrieve the file's size in bytes
   *
   * @return  int size filesize in bytes
   * @throws  io.IOException in case of an error
   */
  public function size(): int {
    if (false === ($stat= $this->_fd ? fstat($this->_fd) : stat($this->uri))) {
      $e= new IOException('Cannot get filesize for '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $stat[7];
  }
  
  /**
   * Truncate the file to the specified length
   *
   * @param   bool TRUE if method succeeded
   * @throws  io.IOException in case of an error
   */
  public function truncate($size= 0): bool {
    if (false === ($return= ftruncate($this->_fd, $size))) {
      $e= new IOException('Cannot truncate file '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $return;
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
      $e= new IOException('Cannot get atime for '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
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
      $e= new IOException('Cannot get mtime for '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $mtime;
  }
  
  /**
   * Set last modification time
   *
   * @param   int time default -1 Unix-timestamp
   * @return  bool success
   * @throws  io.IOException in case of an error
   */
  public function touch($time= -1) {
    if (-1 == $time) $time= time();
    if (false === touch($this->uri, $time)) {
      $e= new IOException('Cannot set mtime for '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return true;
  }

  /**
   * Retrieve when the file was created
   *
   * @return  int The date the file was created as a unix-timestamp
   * @throws  io.IOException in case of an error
   */
  public function createdAt() {
    if (false === ($mtime= filectime($this->uri))) {
      $e= new IOException('Cannot get mtime for '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $mtime;
  }

  /**
   * Read one line and chop off trailing CR and LF characters
   *
   * Returns a string of up to length - 1 bytes read from the file. 
   * Reading ends when length - 1 bytes have been read, on a newline (which is 
   * included in the return value), or on EOF (whichever comes first). 
   *
   * @param   int bytes default 4096 Max. amount of bytes to be read
   * @return  string Data read
   * @throws  io.IOException in case of an error
   */
  public function readLine($bytes= 4096) {
    $bytes= $this->gets($bytes);
    return false === $bytes ? false : chop($bytes);
  }
  
  /**
   * Read one char
   *
   * @return  string the character read
   * @throws  io.IOException in case of an error
   */
  public function readChar() {
    if (false === ($result= fgetc($this->_fd)) && !feof($this->_fd)) {
      $e= new IOException('Cannot read 1 byte from '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $result;
  }

  /**
   * Read a line
   *
   * This function is identical to readLine except that trailing CR and LF characters
   * will be included in its return value
   *
   * @param   int bytes default 4096 Max. amount of bytes to read
   * @return  string Data read
   * @throws  io.IOException in case of an error
   */
  public function gets($bytes= 4096) {
    if (0 === $bytes) return '';
    if (false === ($result= fgets($this->_fd, $bytes)) && !feof($this->_fd)) {
      $e= new IOException('Cannot read '.$bytes.' bytes from '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $result;
  }

  /**
   * Read (binary-safe) up to a given amount of bytes
   *
   * @param   int bytes default 4096 Max. amount of bytes to read
   * @return  string Data read
   * @throws  io.IOException in case of an error
   */
  public function read($bytes= 4096) {
    if (0 === $bytes) return '';
    if (false === ($result= fread($this->_fd, $bytes)) && !feof($this->_fd)) {
      $e= new IOException('Cannot read '.$bytes.' bytes from '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return '' === $result ? false : $result;
  }

  /**
   * Write
   *
   * @param   string string data to write
   * @return  int number of bytes written
   * @throws  io.IOException in case of an error
   */
  public function write($string) {
    if (!$this->_fd || false === ($result= fwrite($this->_fd, $string))) {
      $e= new IOException('Cannot write '.strlen($string).' bytes to '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $result;
  }

  /**
   * Write a line and append a LF (\n) character
   *
   * @param   string string data default '' to write
   * @return  int number of bytes written
   * @throws  io.IOException in case of an error
   */
  public function writeLine($string= '') {
    if (!$this->_fd || false === ($result= fwrite($this->_fd, $string."\n"))) {
      $e= new IOException('Cannot write '.(strlen($string)+ 1).' bytes to '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $result;
  }
  
  /**
   * Returns whether the file pointer is at the end of the file
   *
   * Hint:
   * Use isOpen() to check if the file is open
   *
   * @see     php://feof
   * @return  bool TRUE when the end of the file is reached
   * @throws  io.IOException in case of an error (e.g., the file's not been opened)
   */
  public function eof() {
    $result= feof($this->_fd);
    if (\xp::errorAt(__FILE__, __LINE__ - 1)) {
      $e= new IOException('Cannot determine eof of '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return $result;
  }
  
  /**
   * Sets the file position indicator for fp to the beginning of the 
   * file stream. 
   * 
   * This function is identical to a call of $f->seek(0, SEEK_SET)
   *
   * @return  bool TRUE if rewind suceeded
   * @throws  io.IOException in case of an error
   */
  public function rewind() {
    if (false === ($result= rewind($this->_fd))) {
      $e= new IOException('Cannot rewind file pointer');
      \xp::gc(__FILE__);
      throw $e;
    }
    return true;
  }
  
  /**
   * Move file pointer to a new position
   *
   * @param   int position default 0 The new position
   * @param   int mode default SEEK_SET 
   * @see     php://fseek
   * @throws  io.IOException in case of an error
   * @return  bool success
   */
  public function seek($position= 0, $mode= SEEK_SET) {
    if (0 != ($result= fseek($this->_fd, $position, $mode))) {
      $e= new IOException('Seek error, position '.$position.' in mode '.$mode);
      \xp::gc(__FILE__);
      throw $e;
    }
    return true;
  }
  
  /**
   * Retrieve file pointer position
   *
   * @return  int position
   * @throws  io.IOException in case of an error
   */
  public function tell() {
    if (false === ($result= ftell($this->_fd))) {
      $e= new IOException('Cannot retrieve file pointer\'s position');
      \xp::gc(__FILE__);
      throw $e;
    }
    return $result;
  }

  /**
   * Private wrapper function for locking
   *
   * Warning:
   * flock() will not work on NFS and many other networked file systems. Check your 
   * operating system documentation for more details. On some operating systems flock() 
   * is implemented at the process level. When using a multithreaded server API like 
   * ISAPI you may not be able to rely on flock() to protect files against other PHP 
   * scripts running in parallel threads of the same server instance! flock() is not 
   * supported on antiquated filesystems like FAT and its derivates and will therefore 
   * always return FALSE under this environments (this is especially true for Windows 98 
   * users). 
   *
   * The optional second argument is set to TRUE if the lock would block (EWOULDBLOCK 
   * errno condition).
   *
   * @param   int op operation (one of the predefined LOCK_* constants)
   * @throws  io.IOException in case of an error
   * @return  bool success
   * @see     php://flock
   */
  protected function _lock($mode) {
    if (false === flock($this->_fd, $mode)) {
      $os= '';
      foreach ([
        LOCK_NB   => 'LOCK_NB',
        LOCK_UN   => 'LOCK_UN', 
        LOCK_EX   => 'LOCK_EX', 
        LOCK_SH   => 'LOCK_SH' 
      ] as $o => $s) {
        if ($mode >= $o) { 
          $os.= ' | '.$s;
          $mode-= $o;
        }
      }
      $e= new IOException('Cannot lock file '.$this->uri.' w/ '.substr($os, 3));
      \xp::gc(__FILE__);
      throw $e;
    }
    
    return true;
  }
  
  /**
   * Acquire a shared lock (reader)
   *
   * @param   bool block default FALSE
   * @see     xp://io.File#_lock
   * @return  bool success
   */
  public function lockShared($block= false) {
    return $this->_lock(LOCK_SH + ($block ? 0 : LOCK_NB));
  }
  
  /**
   * Acquire an exclusive lock (writer)
   *
   * @param   bool block default FALSE
   * @see     xp://io.File#_lock
   * @return  bool success
   */
  public function lockExclusive($block= false) {
    return $this->_lock(LOCK_EX + ($block ? 0 : LOCK_NB));
  }
  
  /**
   * Release a lock (shared or exclusive)
   *
   * @see     xp://io.File#_lock
   * @return  bool success
   */
  public function unLock() {
    return $this->_lock(LOCK_UN);
  }

  /**
   * Close this file
   *
   * @return  bool success
   * @throws  io.IOException if close fails
   */
  public function close() {
    if (!is_resource($this->_fd)) {
      throw new IOException('Cannot close non-opened file '.$this->uri);
    }
    if (false === fclose($this->_fd)) {
      $e= new IOException('Cannot close file '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    
    $this->_fd= null;
    return true;
  }
  
  /**
   * Delete this file
   *
   * Warning: Open files cannot be deleted. Use the close() method to
   * close the file first
   *
   * @return  bool success
   * @throws  io.IOException in case of an error (e.g., lack of permissions)
   * @throws  lang.IllegalStateException in case the file is still open
   */
  public function unlink() {
    if (is_resource($this->_fd)) {
      throw new IllegalStateException('File still open');
    }
    if (false === unlink($this->uri)) {
      $e= new IOException('Cannot delete file '.$this->uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return true;
  }
  
  /**
   * Move this file
   *
   * Warning: Open files cannot be moved. Use the close() method to
   * close the file first
   *
   * @param   var target where to move the file to, either a string, a File or a Folder
   * @return  bool success
   * @throws  io.IOException in case of an error (e.g., lack of permissions)
   * @throws  lang.IllegalStateException in case the file is still open
   */
  public function move($target) {
    if (is_resource($this->_fd)) {
      throw new IllegalStateException('File still open');
    }
    
    if ($target instanceof self) {
      $uri= $target->getURI();
    } else if ($target instanceof Folder) {
      $uri= $target->getURI().$this->getFilename();
    } else {
      $uri= $target;
    }
    
    if (false === rename($this->uri, $uri)) {
      $e= new IOException('Cannot move file '.$this->uri.' to '.$uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    
    $this->setURI($uri);
    return true;
  }
  
  /**
   * Copy this file
   *
   * Warning: Open files cannot be copied. Use the close() method to
   * close the file first
   *
   * @param   var target where to copy the file to, either a string, a File or a Folder
   * @return  bool success
   * @throws  io.IOException in case of an error (e.g., lack of permissions)
   * @throws  lang.IllegalStateException in case the file is still open
   */
  public function copy($target) {
    if (is_resource($this->_fd)) {
      throw new IllegalStateException('File still open');
    }

    if ($target instanceof self) {
      $uri= $target->getURI();
    } else if ($target instanceof Folder) {
      $uri= $target->getURI().$this->getFilename();
    } else {
      $uri= $target;
    }
    
    if (false === copy($this->uri, $uri)) {
      $e= new IOException('Cannot copy file '.$this->uri.' to '.$uri);
      \xp::gc(__FILE__);
      throw $e;
    }
    return true;
  }
  
  /** Change permissions for the file */
  public function setPermissions(int $mode): bool {
    return chmod($this->uri, $mode);
  }
  
  /** Get permission mask of the file */
  public function getPermissions(): int {
    $stat= stat($this->uri);
    return $stat['mode'];
  }

  /** Returns whether a given value is equal to this file */
  public function compareTo($value): int {
    return $value instanceof self
      ? (null === $this->uri ? $this->_fd <=> $value->_fd : $this->uri <=> $value->uri)
      : 1
    ;
  }

  /** Returns a hashcode */
  public function hashCode(): string {
    return null === $this->uri ? (string)$this->_fd : md5($this->uri);
  }

  /** Returns a string representation of this object */
  public function toString(): string {
    return sprintf(
      '%s(uri= %s, mode= %s)',
      nameof($this),
      $this->uri,
      $this->mode
    );
  }
}
