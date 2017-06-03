<?php namespace lang\archive;
 
use lang\{ElementNotFoundException, Value};
use io\{EncapsedStream, FileUtil, File};

/**
 * Archives contain a collection of classes.
 *
 * @test  xp://net.xp_framework.unittest.archive.ArchiveV1Test
 * @test  xp://net.xp_framework.unittest.archive.ArchiveV2Test
 * @test  xp://net.xp_framework.unittest.core.ArchiveClassLoaderTest
 * @see   http://java.sun.com/javase/6/docs/api/java/util/jar/package-summary.html
 */
class Archive implements Value {
  const READ =              0x0000;
  const CREATE =            0x0001;
  const HEADER_SIZE =       0x0100;
  const INDEX_ENTRY_SIZE =  0x0100;

  public
    $file     = null,
    $version  = 2;
  
  public
    $_index  = [];
      
  /**
   * Constructor
   *
   * @param   io.File file
   */
  public function __construct($file) {
    $this->file= $file;
  }
  
  /**
   * Get URI
   *
   * @return  string uri
   */
  public function getURI() {
    return $this->file->getURI();
  }

  /**
   * Add a file by its bytes
   *
   * @param   string id the id under which this entry will be located
   * @param   string bytes
   */
  public function addBytes($id, $bytes) {
    $this->_index[$id]= [strlen($bytes), -1, $bytes];
  }

  /**
   * Add a file
   *
   * @param   string id the id under which this entry will be located
   * @param   io.File file
   */
  public function addFile($id, $file) {
    $bytes= FileUtil::getContents($file);
    $this->_index[$id]= [strlen($bytes), -1, $bytes];
  }
  
  /**
   * Create CCA archive
   *
   * @return  bool success
   */
  public function create() {
    $this->file->write(pack(
      'a3c1V1a248', 
      'CCA',
      $this->version,
      sizeof(array_keys($this->_index)),
      "\0"                  // Reserved for future use
    ));

    // Write index
    $offset= 0;
    foreach (array_keys($this->_index) as $id) {
      $this->file->write(pack(
        'a240V1V1a8',
        $id,
        $this->_index[$id][0],
        $offset,
        "\0"                   // Reserved for future use
      ));
      $offset+= $this->_index[$id][0];
    }

    // Write files
    foreach (array_keys($this->_index) as $id) {
      $this->file->write($this->_index[$id][2]);
    }

    $this->file->close();
    return true;
  }
  
  /**
   * Check whether a given element exists
   *
   * @param   string id the element's id
   * @return  bool TRUE when the element exists
   */
  public function contains($id) {
    return isset($this->_index[$id]);
  }
  
  /**
   * Get entry (iterative use)
   * <code>
   *   $a= new Archive(new File('port.xar'));
   *   $a->open(self::READ);
   *   while ($id= $a->getEntry()) {
   *     var_dump($id);
   *   }
   *   $a->close();
   * </code>
   *
   * @return  string id or FALSE to indicate the pointer is at the end of the list
   */
  public function getEntry() {
    $key= key($this->_index);
    next($this->_index);
    return $key;
  }

  /**
   * Rewind archive
   *
   */
  public function rewind() {
    reset($this->_index);
  }
  
  /**
   * Extract a file's contents
   *
   * @param   string id
   * @return  string content
   * @throws  lang.ElementNotFoundException in case the specified id does not exist
   */
  public function extract($id) {
    if (!$this->contains($id)) {
      throw new ElementNotFoundException('Element "'.$id.'" not contained in this archive');
    }

    // Calculate starting position      
    $pos= (
      self::HEADER_SIZE + 
      sizeof(array_keys($this->_index)) * self::INDEX_ENTRY_SIZE +
      $this->_index[$id][1]
    );
    
    try {
      $this->file->isOpen() || $this->file->open(File::READ);
      $this->file->seek($pos, SEEK_SET);
      $data= $this->file->read($this->_index[$id][0]);
    } catch (\lang\XPException $e) {
      throw new ElementNotFoundException('Element "'.$id.'" cannot be read: '.$e->getMessage());
    }
    
    return $data;
  }
  
  /**
   * Fetches a stream to the file in the archive
   *
   * @param   string id
   * @return  io.Stream
   * @throws  lang.ElementNotFoundException in case the specified id does not exist
   */
  public function getStream($id) {
    if (!$this->contains($id)) {
      throw new ElementNotFoundException('Element "'.$id.'" not contained in this archive');
    }

    // Calculate starting position      
    $pos= (
      self::HEADER_SIZE + 
      sizeof(array_keys($this->_index)) * self::INDEX_ENTRY_SIZE +
      $this->_index[$id][1]
    );
    
    return new EncapsedStream($this->file, $pos, $this->_index[$id][0]);
  }
  
  /**
   * Open this archive
   *
   * @param   int mode default self::READ one of self::READ | self::CREATE
   * @return  bool success
   * @throws  lang.IllegalArgumentException in case an illegal mode was specified
   * @throws  lang.FormatException in case the header is malformed
   */
  public function open($mode) {
    static $unpack= [
      1 => 'a80id/a80*filename/a80*path/V1size/V1offset/a*reserved',
      2 => 'a240id/V1size/V1offset/a*reserved'
    ];
    
    switch ($mode) {
      case self::READ:      // Load
        if ($this->file->isOpen()) {
          $this->file->seek(0, SEEK_SET);
        } else {
          $this->file->open(File::READ);
        }

        // Read header
        $header= $this->file->read(self::HEADER_SIZE);
        $data= unpack('a3id/c1version/V1indexsize/a*reserved', $header);

        // Check header integrity
        if ('CCA' !== $data['id']) {
          $e= new \lang\FormatException(sprintf('Header malformed: "CCA" expected, have "%s"', substr($header, 0, 3)));
          \xp::gc(__FILE__);
          throw $e;
        }
        
        // Copy information
        $this->version= $data['version'];
        
        // Read index
        for ($i= 0; $i < $data['indexsize']; $i++) {
          $entry= unpack($unpack[$this->version], $this->file->read(self::INDEX_ENTRY_SIZE));
          $this->_index[rtrim($entry['id'], "\0")]= [$entry['size'], $entry['offset'], null];
        }
        return true;
        
      case self::CREATE:    // Create
        if ($this->file->isOpen()) {
          return $this->file->tell() > 0 ? $this->file->truncate() : true;
        } else {
          return $this->file->open(File::WRITE);
        }
    }
    
    throw new \lang\IllegalArgumentException('Mode '.$mode.' not recognized');
  }
  
  /**
   * Close this archive
   *
   * @return  bool success
   */
  public function close() {
    return $this->file->close();
  }
  
  /**
   * Checks whether this archive is open
   *
   * @return  bool TRUE when the archive file is open
   */
  public function isOpen() {
    return $this->file->isOpen();
  }

  /** @return string */
  public function toString() {
    return sprintf(
      '%s(version= %s, index size= %d) { %s }',
      nameof($this),
      $this->version,
      sizeof($this->_index),
      $this->file->toString()
    );
  }

  /** @return string */
  public function hashCode() {
    return 'A'.$this->version.$this->file->hashCode();
  }

  /**
   * Compares this archive to a given value
   *
   * @param  var $valie
   * @return int
   */
  public function compareTo($value) {
    if (!($value instanceof self)) return 1;
    if ($c= ($this->version <=> $value->version)) return $c;
    return $this->file->compareTo($value->file);
  }

  /**
   * Destructor
   *
   */
  public function __destruct() {
    $this->file->isOpen() && $this->file->close();
  }
}
