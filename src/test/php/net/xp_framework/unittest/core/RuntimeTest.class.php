<?php namespace net\xp_framework\unittest\core;

use lang\FormatException;
use lang\Process;
use lang\Runtime;
use lang\RuntimeOptions;
use lang\XPClass;

class RuntimeTest extends \unittest\TestCase {

  /**
   * Assertion helper for `asArguments()` calls.
   *
   * @param  string[] $expected
   * @param  lang.RuntimeOptions $actual
   * @return void
   * @throws unittest.AssertionFailedError
   */
  private function assertArguments($expected, $actual) {
    defined('HHVM_VERSION') && array_unshift($expected, '--php');
    $this->assertEquals($expected, $actual->asArguments());
  }

  #[@test]
  public function getExecutable() {
    $exe= Runtime::getInstance()->getExecutable();
    $this->assertInstanceOf(Process::class, $exe);
    $this->assertEquals(getmypid(), $exe->getProcessId());
  }

  #[@test]
  public function standardExtensionAvailable() {
    $this->assertTrue(Runtime::getInstance()->extensionAvailable('standard'));
  }

  #[@test]
  public function nonExistantExtension() {
    $this->assertFalse(Runtime::getInstance()->extensionAvailable(':DOES-NOT-EXIST"'));
  }
 
  #[@test]
  public function startupOptions() {
    $startup= Runtime::getInstance()->startupOptions();
    $this->assertInstanceOf(RuntimeOptions::class, $startup);
  }

  #[@test]
  public function modifiedStartupOptions() {
    $startup= Runtime::getInstance()->startupOptions();
    $modified= Runtime::getInstance()->startupOptions()->withSwitch('n');
    $this->assertNotEquals($startup, $modified);
  }

  #[@test]
  public function bootstrapScript() {
    $bootstrap= Runtime::getInstance()->bootstrapScript();
    $this->assertNotEquals(null, $bootstrap);
  }

  #[@test]
  public function certainBootstrapScript() {
    $bootstrap= Runtime::getInstance()->bootstrapScript('class');
    $this->assertEquals('class-main.php', strstr($bootstrap, 'class-main.php'), $bootstrap);
  }

  #[@test]
  public function mainClass() {
    $main= Runtime::getInstance()->mainClass();
    $this->assertInstanceOf(XPClass::class, $main);
  }

  #[@test]
  public function parseSetting() {
    $startup= Runtime::parseArguments(['-denable_dl=0']);
    $this->assertEquals(['0'], $startup['options']->getSetting('enable_dl'));
  }

  #[@test]
  public function parseSettingToleratesWhitespace() {
    $startup= Runtime::parseArguments(['-d auto_globals_jit=0']);
    $this->assertEquals(['0'], $startup['options']->getSetting('auto_globals_jit'));
  }

  #[@test]
  public function doubleDashEndsOptions() {
    $startup= Runtime::parseArguments(['-q', '--', 'tools/xar.php']);
    $this->assertArguments(['-q'], $startup['options']);
    $this->assertEquals('tools/xar.php', $startup['bootstrap']);
  }

  #[@test]
  public function scriptEndsOptions() {
    $startup= Runtime::parseArguments(['-q', 'tools/xar.php']);
    $this->assertArguments(['-q'], $startup['options']);
    $this->assertEquals('tools/xar.php', $startup['bootstrap']);
  }

  #[@test, @expect(FormatException::class)]
  public function parseUnknownSwtich() {
    Runtime::parseArguments(['-@']);
  }

  #[@test]
  public function parseMultiSetting() {
    $startup= Runtime::parseArguments([
      '-dextension=php_xsl.dll', 
      '-dextension=php_sybase_ct.dll'
    ]);
    $this->assertEquals(
      ['php_xsl.dll', 'php_sybase_ct.dll'],
      $startup['options']->getSetting('extension')
    );
  }

  #[@test]
  public function parseSwitch() {
    $startup= Runtime::parseArguments(['-q']);
    $this->assertTrue($startup['options']->getSwitch('q'));
  }

  #[@test]
  public function memoryUsage() {
    $this->assertEquals(
      \lang\Primitive::$INT, 
      typeof(Runtime::getInstance()->memoryUsage())
    );
  }

  #[@test]
  public function peakMemoryUsage() {
    $this->assertEquals(
      \lang\Primitive::$INT, 
      typeof(Runtime::getInstance()->peakMemoryUsage())
    );
  }

  #[@test]
  public function memoryLimit() {
    $this->assertEquals(
      \lang\Primitive::$INT,
      typeof(Runtime::getInstance()->memoryLimit())
    );
  }
}
