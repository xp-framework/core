<?php namespace lang;

class AnonymousClassLoader extends AbstractClassLoader {
  private $file, $start, $end;

  public function __construct($file, $start, $end) {
    $this->file= $file;
    $this->start= $start;
    $this->end= $end;
  }

  /**
   * Checks whether this loader can provide the requested class
   *
   * @param   string class
   * @return  bool
   */
  public function providesClass($class) {
    return false;
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
    return false;
  }

  /**
   * Load class bytes
   *
   * @param   string name fully qualified class name
   * @return  string
   */
  public function loadClassBytes($name) {
    $fd= fopen($this->file, 'rb');
    $i= 0;
    $bytes= '';
    while ($line= fgets($fd, 8192)) {
      $i++;
      if ($i > $this->end) {
        break;
      } else if ($i >= $this->start) {
        $bytes.= $line;
      }
    }
    fclose($fd);
    return '<?php '.substr($bytes, strpos($bytes, 'new class'));
  }
  
  /**
   * Returns URI suitable for include() given a class name
   *
   * @param   string class
   * @return  string
   */
  protected function classUri($class) {
    return null;
  }

  /**
   * Return a class at the given URI
   *
   * @param   string uri
   * @return  string fully qualified class name, or NULL
   */
  protected function classAtUri($uri) {
    return null;
  }

  /**
   * Fetch instance of classloader by path
   *
   * @param   string path the identifier
   * @return  lang.IClassLoader
   */
  public static function instanceFor($path) {
    sscanf($path, "%d:%d@%[^\r]", $start, $end, $file);
    return new self($file, $start, $end);
  }

  /**
   * Get package contents
   *
   * @param   string package
   * @return  string[] filenames
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
}
