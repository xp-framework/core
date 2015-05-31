<?php namespace xp\unittest\sources;

use lang\ClassLoader;

/**
 * Source that dynamically creates testcases
 */
class EvaluationSource extends AbstractSource {
  private static $uniqId= 0;
  private $testClass= null;
  
  /**
   * Constructor
   *
   * @param   string $src method sourcecode
   */
  public function __construct($src) {

    // Support <?php
    $src= trim($src, ' ;').';';
    if (0 === strncmp($src, '<?php', 5)) {
      $src= substr($src, 6);
    }

    $name= 'xp.unittest.DynamicallyGeneratedTestCase·'.(self::$uniqId++);
    $this->testClass= ClassLoader::defineClass($name, 'unittest.TestCase', [], '{
      #[@test] 
      public function run() { '.$src.' }
    }');
  }

  /**
   * Get all test cases
   *
   * @param   var[] arguments
   * @return  unittest.TestCase[]
   */
  public function testCasesWith($arguments) {
    return [$this->testClass->newInstance('run')];
  }

  /**
   * Creates a string representation of this source
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'['.$this->testClass->toString().']';
  }
}
