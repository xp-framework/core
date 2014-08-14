<?php namespace net\xp_framework\unittest\logging;

use unittest\TestCase;
use util\log\layout\PatternLayout;
use util\log\context\MappedLogContext;


/**
 * TestCase
 *
 * @see      xp://util.log.layout.PatternLayout
 */
class PatternLayoutTest extends TestCase {

  /**
   * Test illegal format token %Q
   * 
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function illegalFormatToken() {
    new PatternLayout('%Q');
  }
 
  /**
   * Test unterminated format token
   * 
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function unterminatedFormatToken() {
    new PatternLayout('%');
  }
  
  /**
   * Creates a new logging event
   *
   * @return  util.log.LoggingEvent
   */
  protected function newLoggingEvent() {
    return new \util\log\LoggingEvent(
      new \util\log\LogCategory('default'), 
      1258733284, 
      1214, 
      \util\log\LogLevel::WARN, 
      array('Hello')
    );   
  }

  /**
   * Test literal percent
   * 
   */
  #[@test]
  public function literalPercent() {
    $this->assertEquals(
      '100%',
      (new PatternLayout('100%%'))->format($this->newLoggingEvent())
    );
  }

  /**
   * Test simple format:
   * <pre>
   *   INFO [default] Hello
   * </pre>
   */
  #[@test]
  public function simpleFormat() {
    $this->assertEquals(
      'WARN [default] Hello',
      (new PatternLayout('%L [%c] %m'))->format($this->newLoggingEvent())
    );
  }

  /**
   * Test default format:
   * <pre>
   *   [16:08:04 1214 warn] Hello
   * </pre>
   */
  #[@test]
  public function defaultFormat() {
    $this->assertEquals(
      '[16:08:04 1214 warn] Hello',
      (new PatternLayout('[%t %p %l] %m'))->format($this->newLoggingEvent())
    );
  }

  /**
   * Test format token %x
   *
   */
  #[@test]
  public function tokenContext() {
    $context= new MappedLogContext();
    $context->put('key1', 'val1');

    $event= new \util\log\LoggingEvent(
      new \util\log\LogCategory('default', \util\log\LogLevel::ALL, $context),
      1258733284,
      1,
      \util\log\LogLevel::INFO,
      array('Hello')
    );

    $this->assertEquals(
      'key1=val1',
      (new PatternLayout('%x'))->format($event)
    );
  }
}
