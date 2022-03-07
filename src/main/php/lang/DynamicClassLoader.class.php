<?php namespace lang;

/**
 * Dynamic class loader to define classes at runtime
 *
 * @see   xp://lang.ClassLoader::defineClass
 * @test  xp://net.xp_framework.unittest.reflection.RuntimeClassDefinitionTest
 * @test  xp://net.xp_framework.unittest.reflection.ClassFromDynamicDefinitionTest
 */
class DynamicClassLoader extends AbstractClassLoader {
  const DEVICE = 1852557578;   // crc32('lang.DynamicClassLoader')

  protected
    $position = 0,
    $current  = '';

  public
    $context  = null;   // Used by PHP internally for stream support

  protected static
    $bytes    = [];
  
  static function __static() {
    stream_wrapper_register('dyn', self::class);
  }

  /**
   * Constructor. 
   *
   * @param   string context
   */
  public function __construct($context= null) {
    $this->path= $this->context= $context;
  }
  
  /**
   * Register new class' bytes
   *
   * @param   string $fqcn
   * @param   string $bytes
   * @param   string $prefix Including opening tag
   */
  public function setClassBytes($fqcn, $bytes, $prefix= '<?php') {
    self::$bytes[$fqcn]= $prefix.' '.$bytes;
  }

  /**
   * Checks whether this loader can provide the requested class
   *
   * @param   string class
   * @return  bool
   */
  public function providesClass($class) {
    return isset(self::$bytes[$class]);
  }

  /**
   * Checks whether this loader can provide the requested resource
   *
   * @param   string filename
   * @return  bool
   */
  public function providesResource($filename) {
    return false;
  }

  /**
   * Checks whether this loader can provide the requested package
   *
   * @param   string package
   * @return  bool
   */
  public function providesPackage($package) {
    $l= strlen($package);
    foreach (array_keys(self::$bytes) as $class) {
      if (0 === strncmp($class, $package, $l)) return true;
    }
    return false;
  }

  /**
   * Load class bytes
   *
   * @param   string name fully qualified class name
   * @return  string
   */
  public function loadClassBytes($name) {
    return self::$bytes[$name];
  }
  
  /**
   * Returns URI suitable for include() given a class name
   *
   * @param   string class
   * @return  string
   */
  protected function classUri($class) {
    return 'dyn://'.$class;
  }

  /**
   * Return a class at the given URI
   *
   * @param   string uri
   * @return  string fully qualified class name, or NULL
   */
  protected function classAtUri($uri) {
    sscanf($uri, 'dyn://%s', $name);
    return isset(self::$bytes[$name]) ? $name : null;
  }

  /**
   * Fetch instance of classloader by path
   *
   * @param   string path the identifier
   * @return  lang.IClassLoader
   */
  public static function instanceFor($path) {
    static $pool= [];
    
    if (!isset($pool[$path])) {
      $pool[$path]= new self($path);
    }
    
    return $pool[$path];
  }

  /**
   * Get package contents
   *
   * @param  ?string $package
   * @return string[]
   */
  public function packageContents($package) {
    return [];
  }

  /**
   * Loads a resource.
   *
   * @param   string filename name of resource
   * @return  string
   * @throws  lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResource($filename) {
    throw new ElementNotFoundException('Could not load resource '.$filename);
  }
  
  /**
   * Retrieve a stream to the resource
   *
   * @param   string filename name of resource
   * @return  io.File
   * @throws  lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResourceAsStream($filename) {
    throw new ElementNotFoundException('Could not load resource '.$filename);
  }
  
  /**
   * Stream wrapper method stream_open
   *
   * @param   string path
   * @param   int mode
   * @param   int options
   * @param   string opened_path
   * @return  bool
   */
  public function stream_open($path, $mode, $options, $opened_path) {
    sscanf($path, 'dyn://%[^$]', $this->current);
    if (!isset(self::$bytes[$this->current])) {
      throw new ElementNotFoundException('Could not load '.$this->current);
    }
    return true;
  }
  
  /**
   * Stream wrapper method stream_read
   *
   * @param   int count
   * @return  string
   */
  public function stream_read($count) {
    $bytes= substr(self::$bytes[$this->current], $this->position, $count);
    $this->position+= strlen($bytes);
    return $bytes;
  }
  
  /**
   * Stream wrapper method stream_eof
   *
   * @return  bool
   */
  public function stream_eof() {
    // Leave function body empty to optimize speed
    // See http://bugs.php.net/40047
    // 
    // return $this->position >= strlen(self::$bytes[$this->current]);
  }
  
  /**
   * Stream wrapper method stream_stat
   *
   * @return  [:string]
   */
  public function stream_stat() {
    return [
      'size' => strlen(self::$bytes[$this->current]),
      'dev'  => self::DEVICE,
      'ino'  => crc32(self::$bytes[$this->current])
    ];
  }

  /**
   * Stream wrapper method stream_seek
   *
   * @param   int offset
   * @param   int whence
   * @return  bool
   */
  public function stream_seek($offset, $whence) {
    switch ($whence) {
      case SEEK_SET: $this->position= $offset; break;
      case SEEK_CUR: $this->position+= $offset; break;
      case SEEK_END: $this->position= strlen(self::$bytes[$this->current]); break;
    }
    return true;
  }

  /**
   * Stream wrapper method stream_tell
   *
   * @return  int offset
   */
  public function stream_tell() {
    return $this->position;
  }
  
  /**
   * Stream wrapper method stream_flush
   *
   * @return  bool
   */
  public function stream_flush() {
    return true;
  }

  /**
   * Stream wrapper method stream_close
   *
   * @return  bool
   */
  public function stream_close() {
    return true;
  }

  /**
   * Stream wrapper method stream_set_option
   *
   * @param  int $option
   * @param  int $arg1
   * @param  int $arg2
   * @return bool
   */
  public function stream_set_option($option, $arg1, $arg2) {
    return true;
  }

  /**
   * Stream wrapper method url_stat
   *
   * @param   string path
   * @return  [:var]
   */
  public function url_stat($path) {
    sscanf($path, 'dyn://%s', $name);
    return [
      'size' => strlen(self::$bytes[$name]),
      'dev'  => self::DEVICE,
      'ino'  => crc32(self::$bytes[$name])
    ];
  }
}
