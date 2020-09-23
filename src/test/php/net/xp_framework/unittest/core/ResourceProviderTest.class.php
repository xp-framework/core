<?php namespace net\xp_framework\unittest\core;

use io\{File, FileNotFoundException, FileUtil};
use lang\ResourceProvider;
use unittest\{Expect, Test};

/**
 * Test resource provider functionality
 *
 * @see  xp://lang.ResourceProvider
 */
class ResourceProviderTest extends \unittest\TestCase {

  #[Test]
  public function translatePathWorksWithoutModule() {
    $this->assertEquals('some/where/file.xsl', ResourceProvider::getInstance()->translatePath('res://some/where/file.xsl'));
  }

  #[Test]
  public function loadingAsFile() {
    $this->assertEquals('Foobar', trim(FileUtil::read(new File('res://net/xp_framework/unittest/core/resourceprovider/one/Dummy.txt'))));
  }

  #[Test, Expect(FileNotFoundException::class)]
  public function loadingNonexistantFile() {
    $this->assertEquals('Foobar', trim(FileUtil::read(new File('res://one/Dummy.txt'))));
  }
}