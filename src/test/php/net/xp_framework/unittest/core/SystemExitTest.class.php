<?php namespace net\xp_framework\unittest\core;

use lang\System;
use lang\ClassLoader;
use lang\SystemExit;

/**
 * TestCase for System exit
 *
 * @see      xp://lang.SystemExit
 */
class SystemExitTest extends \unittest\TestCase {
  protected static $exiterClass;

  #[@beforeClass]
  public static function defineExiterClass() {
    self::$exiterClass= ClassLoader::defineClass('net.xp_framework.unittest.core.Exiter', null, [], '{
      public function __construct() { throw new \lang\SystemExit(0); }
      public static function doExit() { new self(); }
    }');
  }

  #[@test]
  public function noStack() {
    $this->assertEquals([], (new SystemExit(0))->getStackTrace());
  }

  #[@test]
  public function zeroExitCode() {
    $this->assertEquals(0, (new SystemExit(0))->getCode());
  }

  #[@test]
  public function nonZeroExitCode() {
    $this->assertEquals(1, (new SystemExit(1))->getCode());
  }

  #[@test]
  public function noMessage() {
    $this->assertEquals('', (new SystemExit(0))->getMessage());
  }
  
  #[@test]
  public function message() {
    $this->assertEquals('Hello', (new SystemExit(1, 'Hello'))->getMessage());
  }
  
  #[@test]
  public function invoke() {
    try {
      self::$exiterClass->getMethod('doExit')->invoke(NULL);
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      // OK
    }
  }

  #[@test]
  public function construct() {
    try {
      self::$exiterClass->newInstance();
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      // OK
    }
  }

  #[@test]
  public function systemExit() {
    try {
      \lang\Runtime::halt();
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      $this->assertEquals(0, $e->getCode());
    }
  }

  #[@test]
  public function systemExitWithNonZeroExitCode() {
    try {
      \lang\Runtime::halt(127);
      $this->fail('Expected', NULL, 'lang.SystemExit');
    } catch (SystemExit $e) {
      $this->assertEquals(127, $e->getCode());
    }
  }
}
