<?php namespace net\xp_framework\unittest\img;

use unittest\TestCase;
use io\Stream;
use io\FileUtil;
use img\Image;
use io\streams\MemoryOutputStream;


/**
 * Tests writing images
 *
 * @see  xp://img.io.ImageWriter
 */
abstract class AbstractImageWriterTest extends TestCase {
  protected $image= null;

  /**
   * Returns the image type to test for
   *
   * @return string
   */
  protected abstract function imageType();

  /**
   * Setup this test. Creates a 1x1 pixel image filled with white.
   */
  public function setUp() {
    if (!\lang\Runtime::getInstance()->extensionAvailable('gd')) {
      throw new \unittest\PrerequisitesNotMetError('GD extension not available');
    }
    $type= $this->imageType();
    if (!(imagetypes() & constant('IMG_'.$type))) {
      throw new \unittest\PrerequisitesNotMetError($type.' support not enabled');
    }
    $this->image= Image::create(1, 1);
    $this->image->fill($this->image->allocate(new \img\Color('#ffffff')));
  }

  /**
   * Tears down this test
   *
   */
  public function tearDown() {
    delete($this->image);
  }
}