<?php namespace lang;

use io\File;

/**
 * Provides a resource through a scheme.
 *
 * @see   xp://lang.ClassLoader
 * @test  xp://net.xp_framework.unittest.core.ResourceProviderTest
 */
class ResourceProvider {
  protected static $instance= null;
  protected $resource= null;
  public $context= null;

  static function __static() {
    stream_wrapper_register('res', self::class);
    self::$instance= new self();
  }

  static function __import($scope) {
    // NOOP
  }

  /** Retrieve instance */
  public static function getInstance(): self { return self::$instance; }

  /**
   * Opens new stream
   *
   * @param   string path
   * @param   string mode
   * @param   int options
   * @param   &string opened_path
   * @return  bool
   */
  public function stream_open($path, $mode, $options, &$opened_path) {
    if ($mode !== 'r' && $mode !== 'rb') return false;

    $this->resource= $this->getLoader()->getResourceAsStream(self::$instance->translatePath($path));
    $this->resource->open(File::READ);

    return true;
  }
  
  /** Retrieve associated loader */
  protected function getLoader(): IClassLoader {
    return ClassLoader::getDefault();
  }

  /**
   * Translate module name into path
   *
   * @param   string path
   * @return  string
   */
  public function translatePath($path) {

    // Shortcut
    if (1 === sscanf($path, 'res://%s', $file)) return $file;
    throw new IllegalArgumentException('Invalid resource expression: "'.$path.'"');
  }
  
  /**
   * Close stream
   *
   * @return void
   */
  public function stream_close() {
    $this->resource->close();
    $this->resource= null;
  }
  
  /**
   * Read from stream
   *
   * @param   int count
   * @return  string
   */
  public function stream_read($count) {
    return $this->resource->read($count);
  }
  
  /**
   * Write to stream. Unsupported
   *
   * @param   string data
   * @return  int
   */
  public function stream_write($data) {
    throw new MethodNotImplementedException('Not writeable.', __METHOD__);
  }
  
  /**
   * Checks for end-of-file
   *
   * @return  bool
   */
  public function stream_eof() {
    return $this->resource->eof();
  }
  
  /**
   * Retrieve current file pointer position
   *
   * @return  int
   */
  public function stream_tell() {
    return $this->resource->tell();
  }
  
  /**
   * Seek to given offset
   *
   * @param   int offset
   * @param   int whence
   */
  public function stream_seek($offset, $whence) {
    $this->resource->seek($offset);
  }
  
  /**
   * Flush stream
   *
   */
  public function stream_flush() {
    // NOOP
  }

  /**
   * Callback for fstat() requests
   *
   * @return  [:int]
   */
  public function stream_stat() {
    return [
      'dev'   => 0,
      'ino'   => 0,
      'mode'  => 0444,
      'nlink' => 0,
      'uid'   => 1,
      'gid'   => 1,
      'rdev'  => 0,
      'size'  => $this->resource->size(),
      'atime' => 0,
      'mtime' => 0,
      'ctime' => 0,
    ];
  }
  
  /**
   * Callback for stat() requests
   *
   * @param   string path
   * @param   int flags
   * @return  <string,int>[]
   */
  public function url_stat($path, $flags) {
    if (!self::$instance->getLoader()->providesResource(self::$instance->translatePath($path))) {
      return false;
    }

    $hdl= self::$instance->getLoader()->getResourceAsStream(self::$instance->translatePath($path));
    return [
      'dev'   => 0,
      'ino'   => 0,
      'mode'  => 0444,
      'nlink' => 0,
      'uid'   => 1,
      'gid'   => 1,
      'rdev'  => 0,
      'size'  => $hdl->size(),
      'atime' => 0,
      'mtime' => 0,
      'ctime' => 0,
    ];
  }
}
