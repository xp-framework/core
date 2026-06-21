<?php namespace lang\archive;

use io\File;
use lang\{AbstractClassLoader, ElementNotFoundException, FormatException};

/** 
 * Loads XP classes from a XAR (XP Archive). Implemented using raw file access
 * to reduce bootstrapping dependencies.
 * 
 * ```php
 * $l= new ArchiveClassLoader(new Archive(new File('classes.xar')));
 * try {
 *   $class= $l->loadClass($argv[1]);
 * } catch (ClassNotFoundException $e) {
 *   $e->printStackTrace();
 *   exit(-1);
 * }
 * 
 * $instance= $class->newInstance();
 * ```
 *
 * @test  lang.unittest.ArchiveClassLoaderTest
 * @test  lang.unittest.ClassFromArchiveTest
 * @see   lang.ClassLoader
 * @see   lang.archive.Archive
 */
class ArchiveClassLoader extends AbstractClassLoader {
  protected $archive;
  protected $acquired= null;
  
  /**
   * Constructor
   * 
   * @param  string|lang.archive.Archive $archive
   */
  public function __construct($archive) {
    $this->path= $archive instanceof Archive ? $archive->getURI() : $archive;

    // Archive within an archive
    if (0 === strncmp('xar://', $this->path, 6)) {
      $this->path= urlencode($this->path);
    }
    $this->archive= 'xar://'.$this->path.'?';
  }

  /** Acquires the archive, opening it if necessary */
  private function acquire() {
    static $unpack= [
      1 => 'a80id/a80*filename/a80*path/V1size/V1offset/a*reserved',
      2 => 'a240id/V1size/V1offset/a*reserved'
    ];

    if (null === $this->acquired) {
      $file= urldecode(substr($this->archive, 6, -1));
      if ('/' === $file[0] && ':' === $file[2]) {
        $file= substr($file, 1);    // Handle xar:///f:/archive.xar => f:/archive.xar
      }

      $fd= fopen($file, 'rb');
      $header= unpack('a3id/c1version/V1indexsize/a*reserved', fread($fd, 0x0100));
      if ('CCA' !== $header['id']) {
        fclose($fd);
        throw new FormatException('Malformed archive '.$archive);
      }

      for ($index= [], $i= 0; $i < $header['indexsize']; $i++) {
        $entry= unpack($unpack[$header['version']], fread($fd, 0x0100));
        $index[rtrim($entry['id'], "\0")]= [$entry['size'], $entry['offset'], $i];
      }

      $this->acquired= ['handle' => $fd, 'index' => $index, 'offset' => 0x0100 + $i * 0x0100];
    }
    return $this->acquired;
  }

  /** Returns file contents, or NULL if it does not exist */
  private function contents($filename) {
    $acquired= $this->acquire();
    if ($file= $acquired['index'][$filename] ?? null) {
      fseek($acquired['handle'], $acquired['offset'] + $file[1], SEEK_SET);

      $bytes= '';
      while ($read= ($file[0] - strlen($bytes)) > 0) {
        if (false === ($chunk= fread($acquired['handle'], $read))) break;
        $bytes.= $chunk;
      }
      return $bytes;
    }
    return null;
  }

  /**
   * Load class bytes
   *
   * @param   string name fully qualified class name
   * @return  string
   */
  public function loadClassBytes($name) {
    return $this->contents(strtr($name, '.', '/').\xp::CLASS_FILE_EXT) ?? '';
  }
  
  /**
   * Returns URI suitable for include() given a class name
   *
   * @param   string class
   * @return  string
   */
  protected function classUri($class) {
    return $this->archive.strtr($class, '.', '/').\xp::CLASS_FILE_EXT;
  }

  /**
   * Return a class at the given URI
   *
   * @param   string uri
   * @return  string fully qualified class name, or NULL
   */
  protected function classAtUri($uri) {
    if (0 !== substr_compare($uri, \xp::CLASS_FILE_EXT, -strlen(\xp::CLASS_FILE_EXT))) return null;

    // Absolute URIs have the form "xar://containing.xar?the/classes/Name.class.php"
    if ((DIRECTORY_SEPARATOR === $uri[0] || (':' === $uri[1] && '\\' === $uri[2]))) {
      return null;
    } else if (false !== ($p= strpos($uri, '?'))) {
      $archive= substr($uri, 0, $p + 1);
      if ($archive !== $this->archive) return null;
      $uri= substr($uri, $p + 1);
    } else {
      $archive= $this->archive;
    }

    // Normalize path: Force forward slashes, strip out "." and empty elements,
    // interpret ".." by backing up until last forward slash is found.
    $path= '';
    foreach (explode('/', strtr($uri, DIRECTORY_SEPARATOR, '/')) as $element) {
      if ('' === $element || '.' === $element) {
        // NOOP
      } else if ('..' === $element) {
        $path= substr($path, 0, strrpos($path, '/'));
      } else {
        $path.= '/'.$element;
      }
    }

    return isset($this->acquire()['index'][substr($path, 1)])
      ? strtr(substr($path, 1, -strlen(\xp::CLASS_FILE_EXT)), '/', '.')
      : null
    ;
  }

  /**
   * Loads a resource.
   *
   * @param   string string name of resource
   * @return  string
   * @throws  lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResource($string) {
    if (null !== ($contents= $this->contents($string))) {
      return $contents;
    }

    throw new ElementNotFoundException('Could not load resource '.$string);
  }
  
  /**
   * Retrieve a stream to the resource
   *
   * @param   string string name of resource
   * @return  io.File
   * @throws  lang.ElementNotFoundException in case the resource cannot be found
   */
  public function getResourceAsStream($string) {
    if (isset($this->acquire()['index'][$string])) {
      return new File($this->archive.$string);
    }

    throw new ElementNotFoundException('Could not load resource '.$string);
  }
  
  /**
   * Checks whether this loader can provide the requested class
   *
   * @param   string class
   * @return  bool
   */
  public function providesClass($class) {
    return isset($this->acquire()['index'][strtr((string)$class, '.', '/').\xp::CLASS_FILE_EXT]);
  }

  /**
   * Checks whether this loader can provide the requested resource
   *
   * @param   string filename
   * @return  bool
   */
  public function providesResource($filename) {
    return isset($this->acquire()['index'][$filename]);
  }

  /**
   * Checks whether this loader can provide the requested package
   *
   * @param   string package
   * @return  bool
   */
  public function providesPackage($package) {
    $acquired= $this->acquire();
    $cmps= strtr($package, '.', '/').'/';
    $cmpl= strlen($cmps);
    
    foreach (array_keys($acquired['index']) as $e) {
      if (strncmp($cmps, $e, $cmpl) === 0) return true;
    }
    return false;
  }
  
  /**
   * Fetch instance of classloader by the path to the archive
   *
   * @param   string path
   * @param   bool expand default TRUE whether to expand the path using realpath
   * @return  lang.archive.ArchiveClassLoader
   */
  public static function instanceFor($path, $expand= true) {
    static $pool= [];
    
    $path= $expand && 0 !== strncmp('xar://', urldecode($path), 6) ? realpath($path) : $path;
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
    $contents= [];
    $acquired= $this->acquire();
    $cmps= strtr((string)$package, '.', '/');
    $cmpl= strlen($cmps);
    
    foreach (array_keys($acquired['index']) as $e) {
      if (strncmp($cmps, $e, $cmpl) != 0) continue;
      $entry= 0 != $cmpl ? substr($e, $cmpl+ 1) : $e;
      
      // Check to see if we're getting something in a subpackage. Imagine the 
      // following structure:
      //
      // archive.xar
      // - tests/ClassOne.class.php
      // - tests/classes/RecursionTest.class.php
      // - tests/classes/ng/NextGenerationRecursionTest.class.php
      //
      // When this method is invoked with "tests" as name, "ClassOne.class.php"
      // and "classes/" should be returned (but neither any of the subdirectories
      // nor their contents)
      if (false !== ($p= strpos($entry, '/'))) {
        $entry= substr($entry, 0, $p);
        if (strstr($entry, '/')) continue;
        $entry.= '/';
      }
      $contents[$entry]= null;
    }
    return array_keys($contents);
  }

  /** Ensures acquired file handle is closed */
  public function __destruct() {
    if ($this->acquired) {
      fclose($this->acquired['handle']);
      $this->acquired= null;
    }
  }
}
