<?php namespace xp\unittest\sources;

use util\Properties;
use lang\XPClass;

/**
 * Source that load tests from a .ini file
 */
class PropertySource extends AbstractSource {
  protected $prop= null;
  protected $descr= null;
  
  /**
   * Constructor
   *
   * @param   util.Properties prop
   */
  public function __construct(Properties $prop) {
    $this->prop= $prop;
    $this->descr= $this->prop->readString('this', 'description', 'Tests');
  }

  /**
   * Get all test cases
   *
   * @param   var[] arguments
   * @return  unittest.TestCase[]
   */
  public function testCasesWith($arguments) {
    $r= [];
    $section= $this->prop->getFirstSection();
    do {
      if ('this' == $section) continue;   // Ignore special section
      $r= array_merge($r, $this->testCasesInClass(
        XPClass::forName($this->prop->readString($section, 'class')),
        $arguments ? $arguments : $this->prop->readArray($section, 'args')
      ));
    } while ($section= $this->prop->getNextSection());
    return $r;
  }

  /**
   * Creates a string representation of this source
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'['.$this->descr.' @ '.$this->prop->getFilename().']';
  }
}
