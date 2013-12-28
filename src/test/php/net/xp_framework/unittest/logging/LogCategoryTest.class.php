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
   *
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
   * @param   string method
   * @param   mixed[] args default ["Argument"]
   * @throws  unittest.AssertionFailedError
   */
  protected function assertLogged($result, $func) {
    $app= $this->cat->addAppender($this->mockAppender());
    $func($this->cat);
    $this->assertEquals($result, $app->messages);
  }
  
  /**
   * Ensure the logger category initially has no appenders
   *
   */
  #[@test]
  public function initiallyNoAppenders() {
    $this->assertFalse($this->cat->hasAppenders());
  }

  /**
   * Tests adding an appender returns the added appender
   *
   */
  #[@test]
  public function addAppenderReturnsAddedAppender() {
    $appender= $this->mockAppender();
    $this->assertEquals($appender, $this->cat->addAppender($appender));
  }

  /**
   * Tests adding an appender returns the log category
   *
   */
  #[@test]
  public function withAppenderReturnsCategory() {
    $this->assertEquals($this->cat, $this->cat->withAppender($this->mockAppender()));
  }

  /**
   * Tests hasAppenders() and addAppender() methods
   *
   */
  #[@test]
  public function hasAppendersAfterAdding() {
    $this->cat->addAppender($this->mockAppender());
    $this->assertTrue($this->cat->hasAppenders());
  }

  /**
   * Tests hasAppenders() and removeAppender() methods
   *
   */
  #[@test]
  public function hasNoMoreAppendersAfterRemoving() {
    $a= $this->cat->addAppender($this->mockAppender());
    $this->cat->removeAppender($a);
    $this->assertFalse($this->cat->hasAppenders());
  }

  /**
   * Tests addAppender() method
   *
   */
  #[@test]
  public function addAppenderTwice() {
    $a= $this->mockAppender();
    $this->cat->addAppender($a);
    $this->cat->addAppender($a);
    $this->cat->removeAppender($a);
    $this->assertFalse($this->cat->hasAppenders());
  }

  /**
   * Tests addAppender() and removeAppender() methods
   *
   */
  #[@test]
  public function addAppenderTwiceWithDifferentFlags() {
    $a= $this->mockAppender();
    $this->cat->addAppender($a, \util\log\LogLevel::INFO);
    $this->cat->addAppender($a, \util\log\LogLevel::WARN);
    $this->cat->removeAppender($a, \util\log\LogLevel::INFO);
    $this->assertTrue($this->cat->hasAppenders());
    $this->cat->removeAppender($a, \util\log\LogLevel::WARN);
    $this->assertFalse($this->cat->hasAppenders());
  }

  /**
   * Tests adding an appender sets default layout if appender does not
   * have a layout.
   *
   */
  #[@test]
  public function addAppenderSetsDefaultLayout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->addAppender($appender);
    $this->assertClass($appender->getLayout(), 'util.log.layout.DefaultLayout');
  }

  /**
   * Tests adding an appender does not overwrite layout
   *
   */
  #[@test]
  public function addAppenderDoesNotOverwriteLayout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->addAppender($appender->withLayout(new PatternLayout('%m')));
    $this->assertClass($appender->getLayout(), 'util.log.layout.PatternLayout');
  }

  /**
   * Tests adding an appender sets default layout if appender does not
   * have a layout.
   *
   */
  #[@test]
  public function withAppenderSetsLayout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->withAppender($appender);
    $this->assertClass($appender->getLayout(), 'util.log.layout.DefaultLayout');
  }

  /**
   * Tests adding an appender does not overwrite layout
   *
   */
  #[@test]
  public function withAppenderDoesNotOverwriteLayout() {
    $appender= newinstance('util.log.Appender', array(), '{
      public function append(LoggingEvent $event) { }
    }');
    $this->cat->withAppender($appender->withLayout(new PatternLayout('%m')));
    $this->assertClass($appender->getLayout(), 'util.log.layout.PatternLayout');
  }

  /**
   * Tests equals() method
   *
   */
  #[@test]
  public function logCategoriesWithSameIdentifierAreEqual() {
    $this->assertEquals(new \util\log\LogCategory('test'), $this->cat);
  }

  /**
   * Tests equals() method
   *
   */
  #[@test]
  public function logCategoriesDifferingAppendersNotEqual() {
    $this->assertNotEquals(
      new \util\log\LogCategory('test'), 
      $this->cat->withAppender($this->mockAppender())
    );
  }

  /**
   * Tests equals() method
   *
   */
  #[@test]
  public function logCategoriesAppendersDifferingInFlagsNotEqual() {
    $appender= $this->mockAppender();
    $this->assertNotEquals(
      create(new \util\log\LogCategory('test'))->withAppender($appender, \util\log\LogLevel::WARN), 
      $this->cat->withAppender($appender)
    );
  }

  /**
   * Tests equals() method
   *
   */
  #[@test]
  public function logCategoriesSameAppendersEqual() {
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

  /**
   * Tests flags
   *
   */
  #[@test]
  public function warningMessageOnlyGetsAppendedToWarnAppender() {
    $app1= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::INFO);
    $app2= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::WARN);
    $this->cat->warn('Test');
    $this->assertEquals(array(), $app1->messages);
    $this->assertEquals(array(array('warn', 'Test')), $app2->messages); 
  }

  /**
   * Tests getAppenders()
   *
   */
  #[@test]
  public function getAppenders() {
    $appender= $this->mockAppender();
    $this->cat->addAppender($appender);
    $this->assertEquals(array($appender), $this->cat->getAppenders());
  }

  /**
   * Tests getAppenders()
   *
   */
  #[@test]
  public function getAppendersWithoutAppendersAdded() {
    $this->assertEquals(array(), $this->cat->getAppenders());
  }

  /**
   * Tests getAppenders()
   *
   */
  #[@test]
  public function getAllAppendersWhenErrorLevelAppenderExists() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $this->assertEquals(array($appender), $this->cat->getAppenders());
  }

  /**
   * Tests getAppenders()
   *
   */
  #[@test]
  public function getErrorLevelAppendersWhenErrorLevelAppendersExist() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $this->assertEquals(array($appender), $this->cat->getAppenders(\util\log\LogLevel::ERROR));
  }

  /**
   * Tests getAppenders()
   *
   */
  #[@test]
  public function getInfoLevelAppendersWhenOnlyErrorLevelAppendersExist() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $this->assertEquals(array(), $this->cat->getAppenders(\util\log\LogLevel::INFO));
  }

  /**
   * Tests getAppenders()
   *
   */
  #[@test]
  public function getInfoLevelAppendersWhenErrorAndInfoLevelAppenderExists() {
    $appender= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR | \util\log\LogLevel::INFO);
    $this->assertEquals(array($appender), $this->cat->getAppenders(\util\log\LogLevel::INFO));
  }

  /**
   * Tests getAppenders()
   *
   */
  #[@test]
  public function getAllAppenders() {
    $app1= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::ERROR);
    $app2= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::WARN);
    $app3= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::INFO);
    $app4= $this->cat->addAppender($this->mockAppender(), \util\log\LogLevel::DEBUG);
    $this->assertEquals(array($app1, $app2, $app3, $app4), $this->cat->getAppenders());
  }


  /**
   * Tests LogCategory::hasContext()
   *
   */
  #[@test]
  public function hasContext() {
    $this->assertFalse($this->cat->hasContext());
    $this->cat->setContext(new NestedLogContext());
    $this->assertTrue($this->cat->hasContext());
  }

  /**
   * Tests LogCategory::getContext()
   *
   */
  #[@test]
  public function getContext() {
    $context= new NestedLogContext();
    $this->cat->setContext($context);
    $this->assertEquals($context, $this->cat->getContext());
  }
}
