<?php namespace util\log;

use util\cmd\Console;

/**
 * Appender which appends data to console. The data goes to STDERR.
 *
 * Note: STDERR will not be defined in a web server's environment,
 * so using this class will have no effect - have a look at the
 * SyslogAppender or FileAppender classes instead.
 *
 * @test    xp://net.xp_framework.unittest.logging.ConsoleAppenderTest
 * @see     xp://util.log.Appender
 */  
class ConsoleAppender extends Appender {
  protected $writer= null;

  /**
   * Constructor
   */
  public function __construct() {
    $this->writer= Console::$err;
  }
  
  /**
   * Append data
   *
   * @param   util.log.LoggingEvent event
   */ 
  public function append(LoggingEvent $event) {
    $this->writer->write($this->layout->format($event));
  }
}
