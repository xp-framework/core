<?php namespace net\xp_framework\unittest\logging;
 
use util\log\LogCategory;
use util\log\Logger;
use util\log\Appender;
use util\log\LogLevel;
use util\log\LoggingEvent;
use util\log\Context;
use util\log\layout\PatternLayout;
use util\log\context\NestedLogContext;

/**
 * Tests LogCategory class
 */
class LogCategoryTest extends \unittest\TestCase {
  
  /**
   * Create a mock appender which simply stores all messages passed to 
   * its append() method.
   *
   * @return  util.log.Appender
   */
  private function mockAppender() {
    $appender= newinstance('util.log.Appender', [], [
      'messages' => [],
      'append' => function(LoggingEvent $event) {
        $this->messages[]= [
          strtolower(LogLevel::nameOf($event->getLevel())), 
          $this->layout->format($event)
        ];
      }
    ]);
    return $appender->withLayout(new PatternLayout('%m'));
  }

  /**
   * Create an empty appender
   *
   * @return  util.log.Appender
   */
  private function emptyAppender() {
    return newinstance('util.log.Appender', [], [
      'append' => function(LoggingEvent $event) { }
    ]);
  }

  /**
   * Helper method
   *
   * @param   var $expected
   * @param   util.log.LogCategory $cat
   * @param   function(util.log.LogCategory): void $func A function to be called with `$cat` as argument
   * @throws  unittest.AssertionFailedError
   */
  private function assertLogged($result, $cat, $func) {
    $app= $cat->addAppender($this->mockAppender());
    $func($cat);
    $this->assertEquals($result, $app->messages);
  }

  #[@test]
  public function can_create_without_arguments() {
    new LogCategory();
  }

  #[@test]
  public function can_create_with_identifier() {
    new LogCategory('identifier');
  }

  #[@test]
  public function can_create_with_identifier_and_level() {
    new LogCategory('identifier', LogLevel::ALL);
  }

  #[@test]
  public function can_create_with_identifier_level_and_context() {
    new LogCategory('identifier', LogLevel::ALL, newinstance(Context::class, [], [
      'format' => function() { return ''; }
    ]));
  }

  #[@test]
  public function identifier() {
    $this->assertEquals('identifier', (new LogCategory('identifier'))->identifier);
  }

  #[@test]
  public function identifier_defaults_to_default() {
    $this->assertEquals('default', (new LogCategory())->identifier);
  }

  #[@test]
  public function logger_category_initially_has_no_appenders() {
    $this->assertFalse((new LogCategory())->hasAppenders());
  }

  #[@test]
  public function addAappender_returns_added_appender() {
    $appender= $this->mockAppender();
    $this->assertEquals($appender, (new LogCategory())->addAppender($appender));
  }

  #[@test]
  public function withAppender_returns_category() {
    $cat= new LogCategory();
    $this->assertEquals($cat, $cat->withAppender($this->mockAppender()));
  }

  #[@test]
  public function hasAppenders_returns_true_after_adding_an_appender() {
    $cat= new LogCategory();
    $cat->addAppender($this->mockAppender());
    $this->assertTrue($cat->hasAppenders());
  }

  #[@test]
  public function hasAppenders_returns_false_after_removing_added_appender() {
    $cat= new LogCategory();
    $a= $cat->addAppender($this->mockAppender());
    $cat->removeAppender($a);
    $this->assertFalse($cat->hasAppenders());
  }

  #[@test]
  public function adding_appender_twice_with_same_flags_has_no_effect() {
    $cat= new LogCategory();
    $a= $this->mockAppender();
    $cat->addAppender($a);
    $cat->addAppender($a);
    $this->assertEquals([$a], $cat->getAppenders());
  }

  #[@test]
  public function adding_appender_twice_with_differing_flags() {
    $cat= new LogCategory();
    $a= $this->mockAppender();
    $cat->addAppender($a, LogLevel::INFO);
    $cat->addAppender($a, LogLevel::WARN);
    $cat->removeAppender($a, LogLevel::INFO);
    $this->assertTrue($cat->hasAppenders());
    $cat->removeAppender($a, LogLevel::WARN);
    $this->assertFalse($cat->hasAppenders());
  }

  #[@test]
  public function addAppender_sets_layout_if_appender_does_not_have_layout() {
    $cat= new LogCategory();
    $appender= $this->emptyAppender();
    $cat->addAppender($appender);
    $this->assertInstanceOf('util.log.layout.DefaultLayout', $appender->getLayout());
  }

  #[@test]
  public function addAppender_does_not_overwrite_layout() {
    $cat= new LogCategory();
    $appender= $this->emptyAppender();
    $cat->addAppender($appender->withLayout(new PatternLayout('%m')));
    $this->assertInstanceOf('util.log.layout.PatternLayout', $appender->getLayout());
  }

  #[@test]
  public function withAppender_sets_layout_if_appender_does_not_have_layout() {
    $cat= new LogCategory();
    $appender= $this->emptyAppender();
    $cat->withAppender($appender);
    $this->assertInstanceOf('util.log.layout.DefaultLayout', $appender->getLayout());
  }

  #[@test]
  public function withAppender_does_not_overwrite_layout() {
    $cat= new LogCategory();
    $appender= $this->emptyAppender();
    $cat->withAppender($appender->withLayout(new PatternLayout('%m')));
    $this->assertInstanceOf('util.log.layout.PatternLayout', $appender->getLayout());
  }

  #[@test]
  public function log_categories_with_empty_identifiers_are_equal() {
    $this->assertEquals(new LogCategory(), new LogCategory());
  }

  #[@test]
  public function log_categories_with_same_identifiers_are_equal() {
    $this->assertEquals(new LogCategory('test'), new LogCategory('test'));
  }

  #[@test]
  public function log_categories_with_differing_appenders_are_not_equal() {
    $this->assertNotEquals(
      new LogCategory(),
      (new LogCategory())->withAppender($this->mockAppender())
    );
  }

  #[@test]
  public function log_categories_with_appenders_differing_in_flags_are_not_equal() {
    $appender= $this->mockAppender();
    $this->assertNotEquals(
      (new LogCategory())->withAppender($appender, LogLevel::WARN), 
      (new LogCategory())->withAppender($appender)
    );
  }

  #[@test]
  public function log_categories_with_same_appenders_are_equal() {
    $appender= $this->mockAppender();
    $this->assertEquals(
      (new LogCategory())->withAppender($appender), 
      (new LogCategory())->withAppender($appender)
    );
  }

  #[@test]
  public function debug() {
    $this->assertLogged(
      [['debug', 'Test']],
      new LogCategory(),
      function($cat) { $cat->debug('Test'); }
    );
  }

  #[@test]
  public function debugf() {
    $this->assertLogged(
      [['debug', 'Test 123']],
      new LogCategory(),
      function($cat) { $cat->debugf('Test %d', '123'); }
    );
  }

  #[@test]
  public function info() {
    $this->assertLogged(
      [['info', 'Test']],
      new LogCategory(),
      function($cat) { $cat->info('Test'); }
    );
  }

  #[@test]
  public function infof() {
    $this->assertLogged(
      [['info', 'Test 123']],
      new LogCategory(),
      function($cat) { $cat->infof('Test %d', '123'); }
    );
  }

  #[@test]
  public function warn() {
    $this->assertLogged(
      [['warn', 'Test']],
      new LogCategory(),
      function($cat) { $cat->warn('Test'); }
    );
  }

  #[@test]
  public function warnf() {
    $this->assertLogged(
      [['warn', 'Test 123']],
      new LogCategory(),
      function($cat) { $cat->warnf('Test %d', '123'); }
    );
  }

  #[@test]
  public function error() {
    $this->assertLogged(
      [['error', 'Test']],
      new LogCategory(),
      function($cat) { $cat->error('Test'); }
    );
  }

  #[@test]
  public function errorf() {
    $this->assertLogged(
      [['error', 'Test 123']],
      new LogCategory(),
      function($cat) { $cat->errorf('Test %d', '123'); }
    );
  }

  #[@test]
  public function mark() {
    $this->assertLogged(
      [['info', str_repeat('-', 72)]],
      new LogCategory(),
      function($cat) { $cat->mark(); }
    );
  }

  #[@test]
  public function log() {
    $this->assertLogged(
      [['info', 'Test 123']],
      new LogCategory(),
      function($cat) { $cat->log(LogLevel::INFO, ['Test', '123']); }
    );
  }

  #[@test]
  public function warning_message_only_gets_appended_to_warn_appender() {
    $cat= new LogCategory();
    $app1= $cat->addAppender($this->mockAppender(), LogLevel::INFO);
    $app2= $cat->addAppender($this->mockAppender(), LogLevel::WARN);
    $cat->warn('Test');
    $this->assertEquals([], $app1->messages);
    $this->assertEquals([['warn', 'Test']], $app2->messages);
  }

  #[@test]
  public function getAppenders_initially_returns_empty_array() {
    $this->assertEquals([], (new LogCategory())->getAppenders());
  }

  #[@test]
  public function getAppenders_returns_added_appender() {
    $cat= new LogCategory();
    $appender= $this->mockAppender();
    $cat->addAppender($appender);
    $this->assertEquals([$appender], $cat->getAppenders());
  }

  #[@test]
  public function getAppenders_returns_added_appender_with_error_flags() {
    $cat= new LogCategory();
    $appender= $cat->addAppender($this->mockAppender(), LogLevel::ERROR);
    $this->assertEquals([$appender], $cat->getAppenders());
  }

  #[@test]
  public function getAppenders_with_error_flags_returns_added_appender_with_error_flags() {
    $cat= new LogCategory();
    $appender= $cat->addAppender($this->mockAppender(), LogLevel::ERROR);
    $this->assertEquals([$appender], $cat->getAppenders(LogLevel::ERROR));
  }

  #[@test]
  public function getAppenders_with_info_flags_does_not_return_added_appender_with_error_flags() {
    $cat= new LogCategory();
    $appender= $cat->addAppender($this->mockAppender(), LogLevel::ERROR);
    $this->assertEquals([], $cat->getAppenders(LogLevel::INFO));
  }

  #[@test]
  public function getAppenders_with_info_flags_returns_added_appender_with_info_and_error_flags() {
    $cat= new LogCategory();
    $appender= $cat->addAppender($this->mockAppender(), LogLevel::ERROR | LogLevel::INFO);
    $this->assertEquals([$appender], $cat->getAppenders(LogLevel::INFO));
  }

  #[@test]
  public function getAppenders_returns_appenders_with_flags() {
    $cat= new LogCategory();
    $app1= $cat->addAppender($this->mockAppender(), LogLevel::ERROR);
    $app2= $cat->addAppender($this->mockAppender(), LogLevel::WARN);
    $app3= $cat->addAppender($this->mockAppender(), LogLevel::INFO);
    $app4= $cat->addAppender($this->mockAppender(), LogLevel::DEBUG);
    $this->assertEquals([$app1, $app2, $app3, $app4], $cat->getAppenders());
  }

  #[@test]
  public function hasContext_initially_returns_false() {
    $this->assertFalse((new LogCategory())->hasContext());
  }

  #[@test]
  public function hasContext_returns_true_after_setting_context() {
    $cat= new LogCategory();
    $cat->setContext(new NestedLogContext());
    $this->assertTrue($cat->hasContext());
  }

  #[@test]
  public function getContext_returns_context_previously_set_with_setContext() {
    $cat= new LogCategory();
    $context= new NestedLogContext();
    $cat->setContext($context);
    $this->assertEquals($context, $cat->getContext());
  }
}
