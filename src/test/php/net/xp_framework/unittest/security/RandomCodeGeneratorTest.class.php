<?php namespace net\xp_framework\unittest\security;

use unittest\TestCase;
use security\password\RandomCodeGenerator;

/**
 * TestCase
 *
 * @see   xp://security.password.RandomCodeGenerator
 */
class RandomCodeGeneratorTest extends TestCase {
  protected $fixture= null;

  /**
   * Setup test fixture
   *
   */
  public function setUp() {
    $this->fixture= new RandomCodeGenerator(16);
  }
    
  #[@test]
  public function length() {
    $this->assertEquals(16, strlen($this->fixture->generate()));
  }

  #[@test]
  public function format() {
    $this->assertTrue((bool)preg_match('/^[a-z0-9]{16}$/', $this->fixture->generate()));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function zeroLength() {
    new RandomCodeGenerator(0);
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function negativeLength() {
    new RandomCodeGenerator(-1);
  }

  #[@test]
  public function hugeLength() {
    $this->assertEquals(10000, strlen((new RandomCodeGenerator(10000))->generate()));
  }
}
