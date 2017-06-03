<?php namespace util;

use io\{IOException, File};
use io\streams\{InputStream, OutputStream, MemoryInputStream, FileInputStream, TextReader};
use lang\{FormatException, IllegalStateException, ElementNotFoundException};

/**
 * An interface to property-files (aka "ini-files")
 *
 * Property-files syntax is easy.
 * ```ini
 * [section]
 * key1=value
 * key2="value"
 * key3="value|value|value"
 * key4="a:value|b:value"
 * ; comment
 *
 * [section2]
 * key=value
 * ```
 *
 * @test    xp://net.xp_framework.unittest.util.PropertyWritingTest
 * @test    xp://net.xp_framework.unittest.util.StringBasedPropertiesTest
 * @test    xp://net.xp_framework.unittest.util.FileBasedPropertiesTest
 * @see     php://parse_ini_file
 */
class Properties implements PropertyAccess {
  private static $env;
  public $_file, $_data;
  private $expansion= null;

  static function __static() {
    self::$env= (new PropertyExpansion())->expand('env', 'getenv');
  }

  /** Creates a new properties instance from a given file */
  public function __construct(string $filename= null) {
    $this->_file= $filename;
  }

  /**
   * Add expansion `${kind.X}` with a given expansion function `f(X)`
   *
   * @param  string $kind
   * @param  [:var]|function(string): string $expansion
   * @return self
   */
  public function expanding($kind, $expansion) {
    $this->expansion= $this->expansion ?: clone self::$env;

    if ($expansion instanceof \ArrayAccess || (is_array($expansion) && 0 !== key($expansion))) {
      $func= function($name) use($expansion) { return isset($expansion[$name]) ? $expansion[$name] : null; };
    } else {
      $func= cast($expansion, 'function(string): string');
    }

    $this->expansion->expand($kind, $func);
    return $this;
  }

  /**
   * Load from an input stream, e.g. a file
   *
   * @param   io.streams.InputStream|io.Channel|string $in
   * @param   string $charset the charset the stream is encoded in or NULL to trigger autodetection by BOM
   * @return  self
   * @throws  io.IOException
   * @throws  lang.FormatException
   */
  public function load($in, $charset= null): self {
    $reader= new TextReader($in, $charset);
    $expansion= $this->expansion ?: self::$env;
    $this->_data= [];
    $section= null;

    while (null !== ($t= $reader->readLine())) {
      $trimmedToken= trim($t);
      if ('' === $trimmedToken) continue;                   // Empty lines
      $c= $trimmedToken{0};
      if (';' === $c || '#' === $c) {                       // One line comments
        continue;                    
      } else if ('[' === $c) {
        if (false === ($p= strrpos($trimmedToken, ']'))) {
          throw new FormatException('Unclosed section "'.$trimmedToken.'"');
        }
        $section= substr($trimmedToken, 1, $p- 1);
        $this->_data[$section]= [];
      } else if (false !== ($p= strpos($t, '='))) {
        $key= trim(substr($t, 0, $p));
        $value= ltrim(substr($t, $p+ 1));
        if ('' === $value) {
          // OK
        } else if ('"' === $value{0}) {                     // Quoted strings
          $quoted= substr($value, 1);
          while (false === ($p= strrpos($quoted, '"'))) {
            if (null === ($line= $reader->readLine())) break;
            $quoted.= "\n".$line;
          }
          $value= $expansion->in(substr($quoted, 0, $p));
        } else {        // unquoted string
          if (false !== ($p= strpos($value, ';'))) {        // Comments at end of line
            $value= substr($value, 0, $p);
          }
          $value= $expansion->in(rtrim($value));
        }

        // Arrays and maps: key[], key[0], key[assoc]
        if (']' === substr($key, -1)) {
          if (false === ($p= strpos($key, '['))) {
            throw new FormatException('Invalid key "'.$key.'"');
          }
          $offset= substr($key, $p+ 1, -1);
          $key= substr($key, 0, $p);
          if (!isset($this->_data[$section][$key])) {
            $this->_data[$section][$key]= [];
          }
          if ('' === $offset) {
            $this->_data[$section][$key][]= $value;
          } else {
            $this->_data[$section][$key][$offset]= $value;
          }
        } else {
          $this->_data[$section][$key]= $value;
        }
      } else if ('' !== trim($t)) {
        throw new FormatException('Invalid line "'.$t.'"');
      }
    }
    return $this;
  }

  /**
   * Quote a value if necessary
   *
   * @param  var $value
   * @return string
   */
  protected function quote($val) {
    return is_string($val) ? '"'.$val.'"' : (string)$val;
  }

  /**
   * Store to an output stream, e.g. a file
   *
   * @param   io.streams.OutputStream out
   * @throws  io.IOException
   */
  public function store(OutputStream $out) {
    foreach (array_keys($this->_data) as $section) {
      $out->write('['.$section."]\n");
      foreach ($this->_data[$section] as $key => $val) {
        if (';' == $key{0}) {
          $out->write("\n; ".$val."\n");
        } else if (is_array($val)) {
          if (empty($val)) {
            $out->write($key."=\n");
          } else if (0 === key($val)) {
            foreach ($val as $v) { $out->write($key.'[]='.$this->quote($v)."\n"); }
          } else {
            foreach ($val as $k => $v) { $out->write($key.'['.$k.']='.$this->quote($v)."\n"); }
          }
        } else {
          $out->write($key.'='.$this->quote($val)."\n");
        }
      }
      $out->write("\n");
    }
  }

  /** Retrieves the file name containing the properties */
  public function getFilename() { return $this->_file; }

  /** Returns whether the property file exists */
  public function exists(): bool { return file_exists($this->_file); }

  /**
   * Create the property file
   *
   * @return  void
   * @throws  io.IOException if the property file could not be created
   */
  public function create() {
    if (null !== $this->_file) {
      $fd= new File($this->_file);
      $fd->open(File::WRITE);
      $fd->close();
    }
    $this->_data= [];
  }

  /**
   * Helper method that loads the data from the file if needed
   *
   * @param   bool force default FALSE
   * @throws  io.IOException
   */
  private function _load($force= false) {
    if ($force || null === $this->_data) {
      $this->load(new FileInputStream($this->_file));
    }
  }

  /**
   * Reload all data from the file
   *
   * @return void
   */
  public function reset() {
    $this->_load(true);
  }

  /** Returns sections */
  public function sections(): \Traversable {
    $this->_load();
    foreach ($this->_data as $section => $_) {
      yield $section;
    }
  }
  
  /**
   * Get the first configuration section
   *
   * @deprecated Use sections() iterator instead
   * @see     xp://util.Properties#getNextSection
   * @return  string the first section's name
   */
  public function getFirstSection() {
    $this->_load();
    reset($this->_data);
    return key($this->_data);
  }
  
  /**
   * Get the next configuration section
   *
   * Example:
   * <code>
   *   if ($section= $prop->getFirstSection()) do {
   *     var_dump($section, $prop->readSection($section));
   *   } while ($section= $prop->getNextSection());
   * </code>
   *
   * @deprecated Use sections() iterator instead
   * @see     xp://util.Properties#getFirstSection
   * @return  var string section or FALSE if this was the last section
   */
  public function getNextSection() {
    $this->_load();
    if (false === next($this->_data)) return false;

    return key($this->_data);
  }
  
  /**
   * Read an entire section into an array
   *
   * @param   string name
   * @param   var[] default default [] what to return in case the section does not exist
   * @return  array
   */
  public function readSection($name, $default= []) {
    $this->_load();
    return $this->_data[$name] ?? $default;
  }
  
  /**
   * Read a value as string
   *
   * @param   string section
   * @param   string key
   * @param   string default default '' what to return in case the section or key does not exist
   * @return  string
   */ 
  public function readString($section, $key, $default= '') {
    $this->_load();
    return $this->_data[$section][$key] ?? $default;
  }
  
  /**
   * Read a value as array
   *
   * @param   string section
   * @param   string key
   * @param   var[] default default NULL what to return in case the section or key does not exist
   * @return  array
   */
  public function readArray($section, $key, $default= []) {
    $this->_load();

    // New: key[]="a" or key[0]="a"
    // Old: key="" (an empty array) or key="a|b|c"
    if (!isset($this->_data[$section][$key])) {
      return $default;
    } else if (is_array($this->_data[$section][$key])) {
      return $this->_data[$section][$key];
    } else {
      return '' == $this->_data[$section][$key] ? [] : explode('|', $this->_data[$section][$key]);
    }
  }

  /**
   * Read a value as maop
   *
   * @param   string section
   * @param   string key
   * @param   [:var] default default NULL what to return in case the section or key does not exist
   * @return  [:var]
   */
  public function readMap($section, $key, $default= null) {
    $this->_load();

    // New: key[color]="green" and key[make]="model"
    // Old: key="color:green|make:model"
    if (!isset($this->_data[$section][$key])) {
      return $default;
    } else if (is_array($this->_data[$section][$key])) {
      return $this->_data[$section][$key];
    } else if ('' === $this->_data[$section][$key]) {
      return [];
    } else {
      $return= [];
      foreach (explode('|', $this->_data[$section][$key]) as $val) {
        if (strstr($val, ':')) {
          list($k, $v)= explode(':', $val, 2);
          $return[$k]= $v;
        } else {
          $return[]= $val;
        } 
      }
      return $return;
    }
  }

  /**
   * Read a value as range
   *
   * @param   string section
   * @param   string key
   * @param   int[] default default NULL what to return in case the section or key does not exist
   * @return  array
   */
  public function readRange($section, $key, $default= []) {
    $this->_load();
    if (!isset($this->_data[$section][$key])) return $default;
    if (2 === sscanf($this->_data[$section][$key], '%d..%d', $min, $max)) {
      return range($min, $max);
    } else {
      return [];
    }
  }
  
  /**
   * Read a value as integer
   *
   * @param   string section
   * @param   string key
   * @param   int default default 0 what to return in case the section or key does not exist
   * @return  int
   */ 
  public function readInteger($section, $key, $default= 0) {
    $this->_load();
    return isset($this->_data[$section][$key])
      ? intval($this->_data[$section][$key])
      : $default
    ;
  }

  /**
   * Read a value as float
   *
   * @param   string section
   * @param   string key
   * @param   float default default 0.0 what to return in case the section or key does not exist
   * @return  float
   */ 
  public function readFloat($section, $key, $default= 0.0) {
    $this->_load();
    return isset($this->_data[$section][$key])
      ? doubleval($this->_data[$section][$key])
      : $default
    ;
  }

  /**
   * Read a value as boolean
   *
   * @param   string section
   * @param   string key
   * @param   bool default default FALSE what to return in case the section or key does not exist
   * @return  bool TRUE, when key is 1, 'on', 'yes' or 'true', FALSE otherwise
   */ 
  public function readBool($section, $key, $default= false) {
    $this->_load();
    if (!isset($this->_data[$section][$key])) return $default;
    return (
      '1' === $this->_data[$section][$key] ||
      0   === strncasecmp('yes', $this->_data[$section][$key], 3) ||
      0   === strncasecmp('true', $this->_data[$section][$key], 4) ||
      0   === strncasecmp('on', $this->_data[$section][$key], 2)
    );
  }
  
  /**
   * Returns whether a section exists
   *
   * @param   string name
   * @return  bool
   */
  public function hasSection($name) {
    $this->_load();
    return isset($this->_data[$name]);
  }

  /**
   * Add a section
   *
   * @param   string name
   * @param   bool overwrite default FALSE whether to overwrite existing sections
   * @return  string name
   */
  public function writeSection($name, $overwrite= false) {
    $this->_load();
    if ($overwrite || !$this->hasSection($name)) $this->_data[$name]= [];
    return $name;
  }
  
  /**
   * Add a string (and the section, if necessary)
   *
   * @param   string section
   * @param   string key
   * @param   string value
   */
  public function writeString($section, $key, $value) {
    $this->_load();
    if (!$this->hasSection($section)) $this->_data[$section]= [];
    $this->_data[$section][$key]= (string)$value;
  }
  
  /**
   * Add a string (and the section, if necessary)
   *
   * @param   string section
   * @param   string key
   * @param   int value
   */
  public function writeInteger($section, $key, $value) {
    $this->_load();
    if (!$this->hasSection($section)) $this->_data[$section]= [];
    $this->_data[$section][$key]= (int)$value;
  }
  
  /**
   * Add a float (and the section, if necessary)
   *
   * @param   string section
   * @param   string key
   * @param   float value
   */
  public function writeFloat($section, $key, $value) {
    $this->_load();
    if (!$this->hasSection($section)) $this->_data[$section]= [];
    $this->_data[$section][$key]= (float)$value;
  }

  /**
   * Add a boolean (and the section, if necessary)
   *
   * @param   string section
   * @param   string key
   * @param   bool value
   */
  public function writeBool($section, $key, $value) {
    $this->_load();
    if (!$this->hasSection($section)) $this->_data[$section]= [];
    $this->_data[$section][$key]= $value ? 'yes' : 'no';
  }
  
  /**
   * Add an array string (and the section, if necessary)
   *
   * @param   string section
   * @param   string key
   * @param   array value
   */
  public function writeArray($section, $key, $value) {
    $this->_load();
    if (!$this->hasSection($section)) $this->_data[$section]= [];
    $this->_data[$section][$key]= $value;
  }

  /**
   * Add a map (and the section, if necessary)
   *
   * @param   string section
   * @param   string key
   * @param   [:var] $value
   */
  public function writeMap($section, $key, $value) {
    $this->_load();
    if (!$this->hasSection($section)) $this->_data[$section]= [];
    $this->_data[$section][$key]= $value;
  }

  /**
   * Add a comment (and the section, if necessary)
   *
   * @param   string section
   * @param   string key
   * @param   string comment
   */
  public function writeComment($section, $comment) {
    $this->_load();
    if (!$this->hasSection($section)) $this->_data[$section]= [];
    $this->_data[$section][';'.sizeof($this->_data[$section])]= $comment;
  }
  
  /**
   * Remove section completely
   *
   * @param   string section
   * @throws  lang.IllegalStateException if given section does not exist
   */
  public function removeSection($section) {
    $this->_load();
    if (!isset($this->_data[$section])) throw new IllegalStateException('Cannot remove nonexistant section "'.$section.'"');
    unset($this->_data[$section]);
  }

  /**
   * Remove key
   *
   * @param   string section
   * @param   string key
   * @throws  lang.IllegalStateException if given key does not exist
   */
  public function removeKey($section, $key) {
    $this->_load();
    if (!isset($this->_data[$section][$key])) throw new IllegalStateException('Cannot remove nonexistant key "'.$key.'" in "'.$section.'"');
    unset($this->_data[$section][$key]);
  }

  /** Check if is equal to other object */
  public function equals($cmp): bool {
    if (!$cmp instanceof self) return false;

    // If based on files, and both base on the same file, then they're equal
    if (null === $this->_data && null === $cmp->_data) {
      return $this->_file === $cmp->_file;
    } else {
      return Objects::equal($this->_data, $cmp->_data);
    }
  }

  /** Creates hashcode */
  public function hashCode(): string {
    return $this->_file.serialize($this->_data);
  }

  /** Creates a string representation of this property file */
  public function toString(): string {
    return nameof($this).'('.$this->_file.')@{'.Objects::stringOf($this->_data).'}';
  }
}
