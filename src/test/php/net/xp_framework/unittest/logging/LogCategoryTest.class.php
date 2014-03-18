<?php namespace net\xp_framework\unittest\logging;
 
use unittest\TestCase;
use util\log\Logger;
use util\log\Appender;
use util\log\LogLevel;
use util\log\layout\PatternLayout;
use util\log\context\NestedLogContext;

/**
 * Tests LogCategory class
 */
class LogCategoryTest extends TestCase {
  public $cat= null;
  
  /**
   * Setup method. Creates logger and cat member for easier access to
   * the Logger instance
   */
  public function setUp() {
    $this->cat= new \util\log\LogCategory('test');
  }
  
  /**
   * Create a mock appender which simply stores all messages passed to 
   * its append() method.
   *
   * @return  util.log.Appender
   */
  protected function mockAppender() {
    $appender= newinstance('util.log.Appender', array(), '{
      public $messages= array();
      
      public function append(LoggingEvent $event) {
        $this->messages[]= array(
          strtolower(LogLevel::nameOf($event->getLevel())), 
          $this->layout->format($event)
        );
      }
    }');
    return $appender->withLayout(new PatternLayout('%m'));
  }

  /**
   * Helper method
   *
   * @param   string $method
   * @param   var $func A function to be called with `$this->cat` as argument
   * @throws  unittest.AssertionFailedError
   */
  protected function assertLogged($result, $func) {
    $app= $this->cat->addAppender($this->mockAppender());
    $func($this->cat);
    $this->assertEquals($result, $app->messages);
  }
  
  #[@test]
  public function logger_category_initially_has_no_appenders() {
    $this->assertFalse($this->cat->hasAppenders());
  }

  #[@test]
  public function addAappender_returns_added_appender() {
    $appender= $this->mockAppender();
    $this->assertEquals($appender, $this->cat->addAppender($appender));
  }

  #[@test]
  public function withAppender_returns_category() {
    $this->assertEquals($this->cat, $this->cat->withAppender($this->mockAppender()));
  }

  #[@test]
  public function hasAppenders_initially_returns_false() {
    $this->assertFalse($this->cat->hasAppenders());
  }

  #[@test]
  public function hasAppenders_returns_true_after_adding_an_appender() {
    $this->cat->addAppender($this->mockAppender());
    $this->assertTrue($this->cat->hasAppenders());
  }

  #[@test]
  public function hasAppenders_returns_false_after_removing_added_appender() {
    $a= $this->cat->addAppender($this->mockAppender());
    $this->cat->removeAppender($a);
    $this->assertFalse($this->cat->hasAppenders());
  }

  #[@test]
  public function adding_appender_twice_with_same_flags_has_no_effect() {
    $a= $this->mockAppender();
    $this->cat->addAppender($a);
    $this->cat->addAppender($a);
    $this->assertEquals(array($a), $this->cat->getAppenders());
  }

  #[@test]
  public function adding_appender_twice_with_differing_flags() {
    $a= $this->mockAppender();
    $this->cat->addAppender($a, \util\log\LogLevel::INFO);
    $this->cat->addAppender($a, \util\log\LogLevel::WARN);
    $this->cat->removeAppender($a, \util\log\LogLevel::INFO);
    $this->assertTrue($this->cat->hasAppenders());
    $this->cat->removeAppender($a, \util\log\LogLevel::WARN);
    $this->assertFalse($this->cat->hasAppenders());
  }

  #[@test]
  public function addAppender_sets_layout_if_appender_does_not_have_layout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->addAppender($appender);
    $this->assertInstanceOf('util.log.layout.DefaultLayout', $appender->getLayout());
  }

  #[@test]
  public function addAppender_does_not_overwrite_layout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->addAppender($appender->withLayout(new PatternLayout('%m')));
    $this->assertInstanceOf('util.log.layout.PatternLayout', $appender->getLayout());
  }

  #[@test]
  public function withAppender_sets_layout_if_appender_does_not_have_layout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->withAppender($appender);
    $this->assertInstanceOf('util.log.layout.DefaultLayout', $appender->getLayout());
  }

  #[@test]
  public function withAppender_does_not_overwrite_layout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->withAppender($appender->withLayout(new PatternLayout('%m')));
    $this->assertInstanceOf('util.log.layout.PatternLayout', $appender->getLayout());
  }

  #[@test]
  public function log_categories_with_same_identifiers_are_equal() {
    $this->assertEquals(new \util\log\LogCategory('test'), $this->cat);
  }

  #[@test]
  public function log_categories_with_differing_appenders_are_not_equal() {
    $this->assertNotEquals(
      new \util\log\LogCategory('test'), 
      $this->cat->withAppender($this->mockAppender())
    );
  }

  #[@test]
  public function log_categories_with_appenders_differing_in_flags_are_not_equal() {
    $appender= $this->mockAppender();
    $this->assertNotEquals(
      create(new \util\log\LogCategory('test'))->withAppender($appender, \util\log\LogLevel::WARN), 
      $this->cat->withAppender($appender)
    );
  }

  #[@test]
  public function log_categories_with_same_appenders_are_equal() {
    $appender= $this->mockAppender();
    $this->assertEquals(
      create(new \util\log\LogCategory('test'))->withAppender($appender), 
      $this->cat->withAppender($appender)
    );
  }

  #[@test]
  public function debug() {
    $this->assertLogged(
      array(array('debug', 'Test')),
      function($cat) { $cat->debug('Test'); }
    );
  }

  #[@test]
  public function debugf() {
    $this->assertLogged(
      array(array('debug', 'Test 123')),
      function($cat) { $cat->debugf('Test %d', '123'); }
    );
  }

  #[@test]
  public function info() {
    $this->assertLogged(
      array(array('info', 'Test')),
      function($cat) { $cat->info('Test'); }
    );
  }

  #[@test]
  public function infof() {
    $this->assertLogged(
      array(array('info', 'Test 123')),
      function($cat) { $cat->infof('Test %d', '123'); }
    );
  }

  #[@test]
  public function warn() {
    $this->assertLogged(
      array(array('warn', 'Test')),
      function($cat) { $cat->warn('Test'); }
    );
  }

  #[@test]
  public function warnf() {
    $this->assertLogged(
      array(array('warn', 'Test 123')),
      function($cat) { $cat->warnf('Test %d', '123'); }
    );
  }

  #[@test]
  public function error() {
    $this->assertLogged(
      array(array('error', 'Test')),
      function($cat) { $cat->error('Test'); }
    );
  }

  #[@test]
  public function errorf() {
    $this->assertLogged(
      array(array('error', 'Test 123')),
      function($cat) { $cat->errorf('Test %d', '123'); }
    );
  }

  #[@test]
  public function mark() {
    $this->assertLogged(
      array(array('info', str_repeat('-', 72))),
      function($cat) { $cat->mark(); }
    );
  }

  #[@test]
  public function log() {
    $this->assertLogged(
      array(array('info', 'Test 123')),
      function($cat) { $cat->log(LogLevel::INFO, array('Test', '123')); }
    );
  }

  #[@test]
  public function warning_message_only_gets_appended_to_warn_appender() {
    $app1= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::INFO);
    $app2= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::WARN);
    $this->cat->warn('Test');
    $this->assertEquals(array(), $app1->messages);
    $this->assertEquals(array(array('warn', 'Test')), $app2->messages); 
  }

  #[@test]
  public function getAppenders_initially_returns_empty_array() {
    $this->assertEquals(array(), $this->cat->getAppenders());
  }

  #[@test]
  public function getAppenders_returns_added_appender() {
    $appender= $this->mockAppender();
    $this->cat->addAppender($appender);
    $this->assertEquals(array($appender), $this->cat->getAppenders());
  }

  #[@test]
  public function getAppenders_returns_added_appender_with_error_flags() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $this->assertEquals(array($appender), $this->cat->getAppenders());
  }

  #[@test]
  public function getAppenders_with_error_flags_returns_added_appender_with_error_flags() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $this->assertEquals(array($appender), $this->cat->getAppenders(\util\log\LogLevel::ERROR));
  }

  #[@test]
  public function getAppenders_with_info_flags_does_not_return_added_appender_with_error_flags() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $this->assertEquals(array(), $this->cat->getAppenders(\util\log\LogLevel::INFO));
  }

  #[@test]
  public function getAppenders_with_info_flags_returns_added_appender_with_info_and_error_flags() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR | \util\log\LogLevel::INFO);
    $this->assertEquals(array($appender), $this->cat->getAppenders(\util\log\LogLevel::INFO));
  }

  #[@test]
  public function getAppenders_returns_appenders_with_flags() {
    $app1= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $app2= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::WARN);
    $app3= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::INFO);
    $app4= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::DEBUG);
    $this->assertEquals(array($app1, $app2, $app3, $app4), $this->cat->getAppenders());
  }

  #[@test]
  public function hasContext_initially_returns_false() {
    $this->assertFalse($this->cat->hasContext());
  }

  #[@test]
  public function hasContext_returns_true_after_setting_context() {
    $this->cat->setContext(new NestedLogContext());
    $this->assertTrue($this->cat->hasContext());
  }

  #[@test]
  public function getContext_returns_context_previously_set_with_setContext() {
    $context= new NestedLogContext();
    $this->cat->setContext($context);
    $this->assertEquals($context, $this->cat->getContext());
  }
}
