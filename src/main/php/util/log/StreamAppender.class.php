<?php namespace util\log;

/**
 * StreamAppender which appends data to a stream
 *
 * @see   xp://util.log.Appender
 * @test  xp://net.xp_framework.unittest.logging.StreamAppenderTest
 */  
class StreamAppender extends Appender {
  public $stream= null;
  
  /**
   * Constructor
   *
   * @param   io.streams.OutputStream stream
   */
  public function __construct(\io\streams\OutputStream $stream) {
    $this->stream= $stream;
  }
  
  /**
   * Append data
   *
   * @param   util.log.LoggingEvent event
   */ 
  public function append(LoggingEvent $event) {
    $this->stream->write($this->layout->format($event));
  }
}
