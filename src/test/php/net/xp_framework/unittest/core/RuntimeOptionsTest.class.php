<?php namespace net\xp_framework\unittest\core;

use lang\RuntimeOptions;

/**
 * TestCase
 *
 * @see    xp://lang.RuntimeOptions
 */
class RuntimeOptionsTest extends \unittest\TestCase {

  /**
   * Assertion helper for `asArguments()` calls.
   *
   * @param  string[] $expected
   * @param  lang.RuntimeOptions $actual
   */
  private function assertArguments($expected, $actual) {
    if (defined('HHVM_VERSION')) {
      array_unshift($expected, '--php');
    }
    $this->assertEquals($expected, $actual->asArguments());
  }

  #[@test]
  public function switchAccessors() {
    $options= new RuntimeOptions();
    $this->assertFalse($options->getSwitch('q'));
    $options->withSwitch('q');
    $this->assertTrue($options->getSwitch('q'));
  }

  #[@test]
  public function getSetting() {
    $options= new RuntimeOptions();
    $this->assertNull($options->getSetting('enable_dl'));
  }

  #[@test]
  public function getSettingWithDefault() {
    $options= new RuntimeOptions();
    $this->assertEquals(0, $options->getSetting('enable_dl', 0));
  }

  #[@test]
  public function settingAccessors() {
    $options= new RuntimeOptions();
    $this->assertNull($options->getSetting('memory_limit'));
    $options->withSetting('memory_limit', ['128M']);
    $this->assertEquals(['128M'], $options->getSetting('memory_limit'));
  }

  #[@test]
  public function settingAccessorsStringOverload() {
    $options= new RuntimeOptions();
    $this->assertNull($options->getSetting('memory_limit'));
    $options->withSetting('memory_limit', '128M');
    $this->assertEquals(['128M'], $options->getSetting('memory_limit'));
  }

  #[@test]
  public function addSetting() {
    $options= new RuntimeOptions();
    $options->withSetting('extension', 'php_xsl.dll', true);
    $options->withSetting('extension', 'php_sybase_ct.dll', true);
    $this->assertEquals(
      ['php_xsl.dll', 'php_sybase_ct.dll'],
      $options->getSetting('extension')
    );
  }

  #[@test]
  public function overwritingSetting() {
    $options= new RuntimeOptions();
    $options->withSetting('extension', 'php_xsl.dll');
    $options->withSetting('extension', 'php_sybase_ct.dll');
    $this->assertEquals(
      ['php_sybase_ct.dll'],
      $options->getSetting('extension')
    );
  }

  #[@test]
  public function removeSetting() {
    $options= new RuntimeOptions();
    $options->withSetting('encoding', 'utf-8');
    $options->withSetting('encoding', null);
    $this->assertNull($options->getSetting('encoding'));
  }

  #[@test]
  public function chainingSwitch() {
    $options= new RuntimeOptions();
    $this->assertTrue($options === $options->withSwitch('q'));
  }

  #[@test]
  public function chainingSetting() {
    $options= new RuntimeOptions();
    $this->assertTrue($options === $options->withSetting('enable_dl', 0));
  }

  #[@test]
  public function getClassPath() {
    $options= new RuntimeOptions();
    $this->assertEquals([], $options->getClassPath());
  }

  #[@test]
  public function withClassPath() {
    $options= new RuntimeOptions();
    $options->withClassPath(['/opt/xp/lib/mysql-1.0.0.xar']);
    $this->assertEquals(['/opt/xp/lib/mysql-1.0.0.xar'], $options->getClassPath());
  }

  #[@test]
  public function withClassPathOverload() {
    $options= new RuntimeOptions();
    $options->withClassPath('/opt/xp/lib/mysql-1.0.0.xar');
    $this->assertEquals(['/opt/xp/lib/mysql-1.0.0.xar'], $options->getClassPath());
  }

  #[@test]
  public function argumentsOnEmptyOptions() {
    $options= new RuntimeOptions();
    $this->assertArguments([], $options);
  }

  #[@test]
  public function argumentsWithSwitch() {
    $options= new RuntimeOptions(); 
    $options->withSwitch('q');
    $this->assertArguments(['-q'], $options);
  }

  #[@test]
  public function argumentsWithSetting() {
    $options= new RuntimeOptions(); 
    $options->withSetting('enable_dl', 0);
    $this->assertArguments(['-d', 'enable_dl=0'], $options);
  }

  #[@test]
  public function argumentsWithMultiSetting() {
    $options= new RuntimeOptions(); 
    $options->withSetting('extension', ['php_xsl.dll', 'php_sybase_ct.dll']);
    $this->assertArguments(
      ['-d', 'extension=php_xsl.dll', '-d', 'extension=php_sybase_ct.dll'],
      $options
    );
  }

  #[@test]
  public function argumentsWithEmptyMultiSetting() {
    $options= new RuntimeOptions(); 
    $options->withSetting('extension', []);
    $this->assertArguments([], $options);
  }

  #[@test]
  public function arguments() {
    $options= (new RuntimeOptions())
      ->withSwitch('q')
      ->withSwitch('n')
      ->withSetting('enable_dl', 1)
      ->withSetting('extension', ['php_xsl.dll', 'php_sybase_ct.dll'])
    ;
    $this->assertArguments(
      ['-q', '-n', '-d', 'enable_dl=1', '-d', 'extension=php_xsl.dll', '-d', 'extension=php_sybase_ct.dll'],
      $options
    );
  }

  #[@test]
  public function classPathDoesntAppearInArguments() {
    $options= new RuntimeOptions(); 
    $options->withClassPath('/opt/xp/lib/mysql-1.0.0.xar');
    $this->assertArguments([], $options);
  }
}
