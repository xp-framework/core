<?php namespace net\xp_framework\unittest\core;

use lang\{ClassLoader, System, SystemExit};
use unittest\{BeforeClass, Test};

/**
 * TestCase for System exit
 *
 * @see      xp://lang.SystemExit
 */
class SystemExitTest extends \unittest\TestCase {
  protected static $exiterClass;

  #[BeforeClass]
  public static function defineExiterClass() {
    self::$exiterClass= ClassLoader::defineClass('net.xp_framework.unittest.core.Exiter', null, [], '{
      public function __construct() { throw new \lang\SystemExit(0); }
      public static function doExit() { new self(); }
    }');
  }

  #[Test]
  public function noStack() {
    $this->assertEquals([], (new SystemExit(0))->getStackTrace());
  }

  #[Test]
  public function zeroExitCode() {
    $this->assertEquals(0, (new SystemExit(0))->getCode());
  }

  #[Test]
  public function nonZeroExitCode() {
    $this->assertEquals(1, (new SystemExit(1))->getCode());
  }

  #[Test]
  public function noMessage() {
    $this->assertEquals('', (new SystemExit(0))->getMessage());
  }
  
  #[Test]
  public function message() {
    $this->assertEquals('Hello', (new SystemExit(1, 'Hello'))->getMessage());
  }
  
  #[Test]
  public function invoke() {
    try {
      self::$exiterClass->getMethod('doExit')->invoke(NULL);
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      // OK
    }
  }

  #[Test]
  public function construct() {
    try {
      self::$exiterClass->newInstance();
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      // OK
    }
  }

  #[Test]
  public function systemExit() {
    try {
      \lang\Runtime::halt();
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      $this->assertEquals(0, $e->getCode());
    }
  }

  #[Test]
  public function systemExitWithNonZeroExitCode() {
    try {
      \lang\Runtime::halt(127);
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      $this->assertEquals(127, $e->getCode());
    }
  }
}