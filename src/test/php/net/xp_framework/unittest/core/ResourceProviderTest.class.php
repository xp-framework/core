<?php namespace net\xp_framework\unittest\core;

use io\File;
use io\FileNotFoundException;
use io\FileUtil;
use lang\ResourceProvider;

/**
 * Test resource provider functionality
 *
 * @see  xp://lang.ResourceProvider
 */
class ResourceProviderTest extends \unittest\TestCase {

  #[@test]
  public function translatePathWorksWithoutModule() {
    $this->assertEquals('some/where/file.xsl', ResourceProvider::getInstance()->translatePath('res://some/where/file.xsl'));
  }

  #[@test]
  public function loadingAsFile() {
    $this->assertEquals('Foobar', trim(FileUtil::read(new File('res://net/xp_framework/unittest/core/resourceprovider/one/Dummy.txt'))));
  }

  #[@test, @expect(FileNotFoundException::class)]
  public function loadingNonexistantFile() {
    $this->assertEquals('Foobar', trim(FileUtil::read(new File('res://one/Dummy.txt'))));
  }
}
