<?php namespace net\xp_framework\unittest\core;

use lang\{FormatException, Process, Runtime, RuntimeOptions, XPClass};
use unittest\{Expect, Test};

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
    $this->assertEquals($expected, $actual->asArguments());
  }

  #[Test]
  public function getExecutable() {
    $exe= Runtime::getInstance()->getExecutable();
    $this->assertInstanceOf(Process::class, $exe);
    $this->assertEquals(getmypid(), $exe->getProcessId());
  }

  #[Test]
  public function standardExtensionAvailable() {
    $this->assertTrue(Runtime::getInstance()->extensionAvailable('standard'));
  }

  #[Test]
  public function nonExistantExtension() {
    $this->assertFalse(Runtime::getInstance()->extensionAvailable(':DOES-NOT-EXIST"'));
  }
 
  #[Test]
  public function startupOptions() {
    $startup= Runtime::getInstance()->startupOptions();
    $this->assertInstanceOf(RuntimeOptions::class, $startup);
  }

  #[Test]
  public function modifiedStartupOptions() {
    $startup= Runtime::getInstance()->startupOptions();
    $modified= Runtime::getInstance()->startupOptions()->withSwitch('n');
    $this->assertNotEquals($startup, $modified);
  }

  #[Test]
  public function bootstrapScript() {
    $bootstrap= Runtime::getInstance()->bootstrapScript();
    $this->assertNotEquals(null, $bootstrap);
  }

  #[Test]
  public function certainBootstrapScript() {
    $bootstrap= Runtime::getInstance()->bootstrapScript('class');
    $this->assertEquals('class-main.php', strstr($bootstrap, 'class-main.php'), $bootstrap);
  }

  #[Test]
  public function mainClass() {
    $main= Runtime::getInstance()->mainClass();
    $this->assertInstanceOf(XPClass::class, $main);
  }

  #[Test]
  public function parseSetting() {
    $startup= Runtime::parseArguments(['-denable_dl=0']);
    $this->assertEquals(['0'], $startup['options']->getSetting('enable_dl'));
  }

  #[Test]
  public function parseSettingToleratesWhitespace() {
    $startup= Runtime::parseArguments(['-d auto_globals_jit=0']);
    $this->assertEquals(['0'], $startup['options']->getSetting('auto_globals_jit'));
  }

  #[Test]
  public function doubleDashEndsOptions() {
    $startup= Runtime::parseArguments(['-q', '--', 'tools/xar.php']);
    $this->assertArguments(['-q'], $startup['options']);
    $this->assertEquals('tools/xar.php', $startup['bootstrap']);
  }

  #[Test]
  public function scriptEndsOptions() {
    $startup= Runtime::parseArguments(['-q', 'tools/xar.php']);
    $this->assertArguments(['-q'], $startup['options']);
    $this->assertEquals('tools/xar.php', $startup['bootstrap']);
  }

  #[Test, Expect(FormatException::class)]
  public function parseUnknownSwtich() {
    Runtime::parseArguments(['-@']);
  }

  #[Test]
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

  #[Test]
  public function parseSwitch() {
    $startup= Runtime::parseArguments(['-q']);
    $this->assertTrue($startup['options']->getSwitch('q'));
  }

  #[Test]
  public function memoryUsage() {
    $this->assertEquals(
      \lang\Primitive::$INT, 
      typeof(Runtime::getInstance()->memoryUsage())
    );
  }

  #[Test]
  public function peakMemoryUsage() {
    $this->assertEquals(
      \lang\Primitive::$INT, 
      typeof(Runtime::getInstance()->peakMemoryUsage())
    );
  }

  #[Test]
  public function memoryLimit() {
    $this->assertEquals(
      \lang\Primitive::$INT,
      typeof(Runtime::getInstance()->memoryLimit())
    );
  }
}