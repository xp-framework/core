<?php namespace net\xp_framework\unittest\logging;
 
use util\log\Logger;

/**
 * Tests Logger class
 */
class LoggerTest extends \unittest\TestCase {
  protected $logger= null;
  
  /**
   * Setup method. Creates logger member for easier access to the
   * Logger instance
   */
  public function setUp() {
    $this->logger= Logger::getInstance();
  }
  
  /**
   * Teardown method. Finalizes the logger.
   */
  public function tearDown() {
    $this->logger->finalize();
  }
  
  #[@test]
  public function loggerIsASingleton() {
    $this->assertTrue($this->logger === Logger::getInstance());
  }

  #[@test]
  public function defaultCategory() {
    with ($cat= $this->logger->getCategory()); {
      $this->assertInstanceOf('util.log.LogCategory', $cat);
      $this->assertFalse($cat->hasAppenders());
    }
  }

  #[@test]
  public function isConfigurable() {
    $this->assertInstanceOf('util.Configurable', $this->logger);
  }

  #[@test]
  public function configureMultipleCategories() {
    $this->logger->configure(\util\Properties::fromString(trim('
[sql]
appenders="util.log.FileAppender"
appender.util.log.FileAppender.params="filename"
appender.util.log.FileAppender.param.filename="/var/log/xp/sql.log"

[remote]
appenders="util.log.FileAppender"
appender.util.log.FileAppender.params="filename"
appender.util.log.FileAppender.param.filename="/var/log/xp/remote.log"
    ')));
    
    with ($sql= $this->logger->getCategory('sql')); {
      $appenders= $sql->getAppenders();
      $this->assertInstanceOf('util.log.FileAppender', $appenders[0]);
      $this->assertEquals('/var/log/xp/sql.log', $appenders[0]->filename);
    }
    
    with ($sql= $this->logger->getCategory('remote')); {
      $appenders= $sql->getAppenders();
      $this->assertInstanceOf('util.log.FileAppender', $appenders[0]);
      $this->assertEquals('/var/log/xp/remote.log', $appenders[0]->filename);
    }
  }

  #[@test]
  public function configureMultipleAppenders() {
    $this->logger->configure(\util\Properties::fromString(trim('
[sql]
appenders="util.log.FileAppender|util.log.SmtpAppender"
appender.util.log.FileAppender.params="filename"
appender.util.log.FileAppender.param.filename="/var/log/xp/sql.log"
appender.util.log.SmtpAppender.params="email"
appender.util.log.SmtpAppender.param.email="xp@example.com"
    ')));
    
    with ($sql= $this->logger->getCategory('sql')); {
      $appenders= $sql->getAppenders();
      $this->assertInstanceOf('util.log.FileAppender', $appenders[0]);
      $this->assertEquals('/var/log/xp/sql.log', $appenders[0]->filename);
      $this->assertInstanceOf('util.log.SmtpAppender', $appenders[1]);
      $this->assertEquals('xp@example.com', $appenders[1]->email);
    }
  }

  #[@test]
  public function configureWithFlags() {
    $this->logger->configure(\util\Properties::fromString(trim('
[sql]
appenders="util.log.FileAppender"
appender.util.log.FileAppender.params="filename"
appender.util.log.FileAppender.param.filename="/var/log/xp/sql-errors_%Y-%m-%d.log"
appender.util.log.FileAppender.flags="LOGGER_FLAG_ERROR|LOGGER_FLAG_WARN"
    ')));
    
    with ($cat= $this->logger->getCategory('sql')); {
      $this->assertFalse($cat === $this->logger->getCategory());
      $this->assertInstanceOf('util.log.LogCategory', $cat);
      $this->assertTrue($cat->hasAppenders());
      with ($appenders= $cat->getAppenders(\util\log\LogLevel::ERROR | \util\log\LogLevel::WARN)); {
        $this->assertInstanceOf('util.log.FileAppender', $appenders[0]);
      }
    }
  }

  #[@test]
  public function configureWithLevels() {
    $this->logger->configure(\util\Properties::fromString(trim('
[sql]
appenders="util.log.FileAppender"
appender.util.log.FileAppender.params="filename"
appender.util.log.FileAppender.param.filename="/var/log/xp/sql-errors_%Y-%m-%d.log"
appender.util.log.FileAppender.levels="ERROR|WARN"
    ')));
    
    with ($cat= $this->logger->getCategory('sql')); {
      $this->assertFalse($cat === $this->logger->getCategory());
      $this->assertInstanceOf('util.log.LogCategory', $cat);
      $this->assertTrue($cat->hasAppenders());
      with ($appenders= $cat->getAppenders(\util\log\LogLevel::ERROR | \util\log\LogLevel::WARN)); {
        $this->assertInstanceOf('util.log.FileAppender', $appenders[0]);
      }
    }
  }

  #[@test]
  public function configureWithContext() {
    $this->logger->configure(\util\Properties::fromString(trim('
[context]
appenders="util.log.FileAppender"
context="util.log.context.NestedLogContext"
appender.util.log.FileAppender.params="filename"
appender.util.log.FileAppender.param.filename="/var/log/xp/default.log"
    ')));

    with ($cat= $this->logger->getCategory('context')); {
      $this->assertTrue($cat->hasContext());
      $this->assertInstanceOf('util.log.context.NestedLogContext', $cat->getContext());
    }
  }
}
