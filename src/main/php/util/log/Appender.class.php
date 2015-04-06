<?php namespace util\log;

/**
 * Abstract base class for appenders
 *
 * @see   xp://util.log.LogCategory#addAppender
 */
abstract class Appender extends \lang\Object {
  protected $layout= null;

  /**
   * Sets layout
   *
   * @param   util.log.Layout layout
   */
  public function setLayout(Layout $layout) {
    $this->layout= $layout;
  }
  
  /**
   * Sets layout and returns this appender
   *
   * @param   util.log.Layout layout
   * @return  util.log.Appender
   */
  public function withLayout(Layout $layout) {
    $this->layout= $layout;
    return $this;
  }

  /**
   * Gets layout
   *
   * @return  util.log.Layout
   */
  public function getLayout() {
    return $this->layout;
  }

  /**
   * Append data
   *
   * @param   util.log.LoggingEvent event
   */ 
  public abstract function append(LoggingEvent $event);
  
  /**
   * Finalize this appender. This method is called when the logger
   * is shut down. Does nothing in this default implementation.
   *
   */   
  public function finalize() { }

  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'(layout= '.\xp::stringOf($this->layout).')';
  }
}
