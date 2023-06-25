<?php namespace lang\unittest;

use lang\{FormatException, Process, Runtime, RuntimeOptions, XPClass};
use unittest\{Assert, Expect, Test};

class RuntimeTest {

  /**
   * Assertion helper for `asArguments()` calls.
   *
   * @param  string[] $expected
   * @param  lang.RuntimeOptions $actual
   * @return void
   * @throws unittest.AssertionFailedError
   */
  private function assertArguments($expected, $actual) {
    Assert::equals($expected, $actual->asArguments());
  }

  #[Test]
  public function getExecutable() {
    $exe= Runtime::getInstance()->getExecutable();
    Assert::instance(Process::class, $exe);
    Assert::equals(getmypid(), $exe->getProcessId());
  }

  #[Test]
  public function standardExtensionAvailable() {
    Assert::true(Runtime::getInstance()->extensionAvailable('standard'));
  }

  #[Test]
  public function nonExistantExtension() {
    Assert::false(Runtime::getInstance()->extensionAvailable(':DOES-NOT-EXIST"'));
  }
 
  #[Test]
  public function startupOptions() {
    $startup= Runtime::getInstance()->startupOptions();
    Assert::instance(RuntimeOptions::class, $startup);
  }

  #[Test]
  public function modifiedStartupOptions() {
    $startup= Runtime::getInstance()->startupOptions();
    $modified= Runtime::getInstance()->startupOptions()->withSwitch('n');
    Assert::notEquals($startup, $modified);
  }

  #[Test]
  public function bootstrapScript() {
    $bootstrap= Runtime::getInstance()->bootstrapScript();
    Assert::notEquals(null, $bootstrap);
  }

  #[Test]
  public function certainBootstrapScript() {
    $bootstrap= Runtime::getInstance()->bootstrapScript('class');
    Assert::equals('class-main.php', strstr($bootstrap, 'class-main.php'), $bootstrap);
  }

  #[Test]
  public function mainClass() {
    $main= Runtime::getInstance()->mainClass();
    Assert::instance(XPClass::class, $main);
  }

  #[Test]
  public function parseSetting() {
    $startup= Runtime::parseArguments(['-denable_dl=0']);
    Assert::equals(['0'], $startup['options']->getSetting('enable_dl'));
  }

  #[Test]
  public function parseSettingToleratesWhitespace() {
    $startup= Runtime::parseArguments(['-d auto_globals_jit=0']);
    Assert::equals(['0'], $startup['options']->getSetting('auto_globals_jit'));
  }

  #[Test]
  public function doubleDashEndsOptions() {
    $startup= Runtime::parseArguments(['-q', '--', 'tools/xar.php']);
    $this->assertArguments(['-q'], $startup['options']);
    Assert::equals('tools/xar.php', $startup['bootstrap']);
  }

  #[Test]
  public function scriptEndsOptions() {
    $startup= Runtime::parseArguments(['-q', 'tools/xar.php']);
    $this->assertArguments(['-q'], $startup['options']);
    Assert::equals('tools/xar.php', $startup['bootstrap']);
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
    Assert::equals(
      ['php_xsl.dll', 'php_sybase_ct.dll'],
      $startup['options']->getSetting('extension')
    );
  }

  #[Test]
  public function parseSwitch() {
    $startup= Runtime::parseArguments(['-q']);
    Assert::true($startup['options']->getSwitch('q'));
  }

  #[Test]
  public function memoryUsage() {
    Assert::equals(
      \lang\Primitive::$INT, 
      typeof(Runtime::getInstance()->memoryUsage())
    );
  }

  #[Test]
  public function peakMemoryUsage() {
    Assert::equals(
      \lang\Primitive::$INT, 
      typeof(Runtime::getInstance()->peakMemoryUsage())
    );
  }

  #[Test]
  public function memoryLimit() {
    Assert::equals(
      \lang\Primitive::$INT,
      typeof(Runtime::getInstance()->memoryLimit())
    );
  }
}