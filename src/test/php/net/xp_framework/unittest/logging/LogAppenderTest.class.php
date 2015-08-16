<?php namespace net\xp_framework\unittest\logging;

use unittest\TestCase;
use util\log\Appender;
use util\log\LogCategory;
use util\log\LoggingEvent;
use util\log\layout\PatternLayout;
use util\collections\Vector;

/**
 * TestCase
 *
 * @see      xp://util.log.Appender
 */
class LogAppenderTest extends TestCase {
  protected $fixture= null;
  protected $events= null;

  /**
   * Sets up test case
   */
  public function setUp() {
    $this->events= create('new util.collections.Vector<string>()');
    $appender= newinstance(Appender::class, [$this->events], [
      'events' => null,
      '__construct' => function($events) { $this->events= $events; },
      'append' => function(LoggingEvent $event) {
        $this->events[]= $this->layout->format($event);
      }
    ]);
    $this->fixture= (new LogCategory('default'))
      ->withAppender($appender->withLayout(new PatternLayout('[%l] %m')))
    ;
  }
  
  #[@test]
  public function info() {
    $this->fixture->info('Hello');
    $this->assertEquals('[info] Hello', $this->events[0]);
  }

  #[@test]
  public function infoWithMultipleArguments() {
    $this->fixture->info('Hello', 'World');
    $this->assertEquals('[info] Hello World', $this->events[0]);
  }

  #[@test]
  public function warn() {
    $this->fixture->warn('Hello');
    $this->assertEquals('[warn] Hello', $this->events[0]);
  }

  #[@test]
  public function debug() {
    $this->fixture->debug('Hello');
    $this->assertEquals('[debug] Hello', $this->events[0]);
  }

  #[@test]
  public function error() {
    $this->fixture->error('Hello');
    $this->assertEquals('[error] Hello', $this->events[0]);
  }
}
