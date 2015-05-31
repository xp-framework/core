<?php namespace io\collections;

/**
 * A composite of multiple collections
 *
 * Example (all files in /home and /usr):
 * ```php
 * $collection= new CollectionComposite([
 *   new FileCollection('/home'),
 *   new FileCollection('/usr')
 * ]);
 * 
 * $collection->open();
 * while (null !== ($element= $collection->next())) {
 *   Console::writeLine('- ', $element->toString());
 * }
 * $collection->close();
 * ```
 *
 * @see      http://news.xp-framework.net/article/129/2006/11/09/
 * @test     xp://net.xp_framework.unittest.io.collections.CollectionCompositeTest 
 */
class CollectionComposite extends \lang\Object {
  public
    $collections = [];
  
  protected
    $_current    = 0;
    
  /**
   * Constructor
   *
   * @param   io.collections.IOCollection[] collections
   * @throws  lang.IllegalArgumentException if collections is an empty array
   */
  public function __construct($collections) {
    if (empty($collections)) {
      throw new \lang\IllegalArgumentException('Collections may not be empty');
    }
    $this->collections= $collections;
  }
  
  /**
   * Open this collection
   *
   */
  public function open() { 
    $this->collections[0]->open();
  }

  /**
   * Rewind this collection (reset internal pointer to beginning of list)
   *
   */
  public function rewind() {
    do {
      $this->collections[$this->_current]->rewind(); 
    } while ($this->_current-- > 0);
  }

  /**
   * Retrieve next element in collection. Return NULL if no more entries
   * are available
   *
   * @return  io.collection.IOElement
   */
  public function next() {
    do { 
      if (null !== ($element= $this->collections[$this->_current]->next())) return $element;
      
      // End of current collection, close it and continue with next collection
      // In case the end of collections has been reached, return NULL
      $this->collections[$this->_current]->close();
      if (++$this->_current >= sizeof($this->collections)) {
        $this->_current--;
        return null;
      }
      $this->collections[$this->_current]->open();
    } while (1);
  }

  /**
   * Close this collection
   *
   */
  public function close() { 
    do {
      $this->collections[$this->_current]->close(); 
    } while ($this->_current-- > 0);
  }

  /**
   * Returns a string representation of this object.
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'(->@'.$this->_current.') '.\xp::stringOf($this->collections);
  }    
}
