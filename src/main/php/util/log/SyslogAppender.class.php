<?php namespace util\log;

/**
 * Appender which appends data to syslog
 *
 * @see  xp://util.log.Appender
 * @see  php://syslog
 */  
class SyslogAppender extends Appender {
  protected static $lastIdentifier= false;
  public $identifier, $facility;

  /**
   * Constructor
   *
   * @param  string $identifier default NULL if omitted, defaults to script's filename
   * @param  int $facility default LOG_USER
   * @see    php://openlog for valid facility values
   */
  public function __construct($identifier= null, $facility= LOG_USER) {
    $this->identifier= $identifier;
    $this->facility= $facility;
  }
  
  /**
   * Append data
   *
   * @param  util.log.LoggingEvent $event
   */ 
  public function append(LoggingEvent $event) {
    static $map= [
      LogLevel::INFO    => LOG_INFO,
      LogLevel::WARN    => LOG_WARNING,
      LogLevel::ERROR   => LOG_ERR,
      LogLevel::DEBUG   => LOG_DEBUG,
      LogLevel::NONE    => LOG_NOTICE
    ];

    if ($this->identifier !== self::$lastIdentifier) {
      closelog();
      openlog(
        $this->identifier ?: basename($_SERVER['PHP_SELF']), 
        LOG_ODELAY | LOG_PID, 
        $this->facility
      );
      self::$lastIdentifier= $this->identifier;
    }

    $l= $event->getLevel();
    syslog($map[isset($map[$l]) ? $l : LogLevel::NONE], $this->layout->format($event));
  }
  
  /**
   * Finalize this appender - is called when the logger shuts down
   * at the end of the request.
   *
   * @return void
   */
  public function finalize() {
    closelog();
    self::$lastIdentifier= false;
  }

  /**
   * Destructor
   */
  public function __destruct() {
    $this->finalize();
  }
}