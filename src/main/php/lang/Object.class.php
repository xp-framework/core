<?php namespace lang;
 
/**
 * Class Object is the root of the class hierarchy. Every class has 
 * Object as a superclass. 
 *
 * @test  xp://net.xp_framework.unittest.core.ObjectTest
 */
class Object implements Generic { use \__xp;
  public $__id;
  
  /**
   * Cloning handler
   */
  public function __clone() {
    $this->__id= uniqid('', true);
  }

  /**
   * Returns a hashcode for this object
   *
   * @return  string
   */
  public function hashCode() {
    if (!$this->__id) $this->__id= uniqid('', true);
    return $this->__id;
  }
  
  /**
   * Indicates whether some other object is "equal to" this one.
   *
   * @param   var $cmp
   * @return  bool TRUE if the compared object is equal to this object
   */
  public function equals($cmp) {
    if (!$cmp instanceof self) return false;
    if (!$this->__id) $this->__id= uniqid('', true);
    if (!$cmp->__id) $cmp->__id= uniqid('', true);
    return $this === $cmp;
  }
  
  /**
   * Returns the runtime class of an object.
   *
   * @return  lang.XPClass runtime class
   * @see     xp://lang.XPClass
   */
  public function getClass() {
    return new XPClass($this);
  }
  
  /**
   * Creates a string representation of this object. In general, the toString 
   * method returns a string that "textually represents" this object. The result 
   * should be a concise but informative representation that is easy for a 
   * person to read. It is recommended that all subclasses override this method.
   * 
   * Per default, this method returns:
   * <xmp>
   *   [fully-qualified-class-name] '{' [members-and-value-list] '}'
   * </xmp>
   * 
   * Example:
   * <xmp>
   *   lang.Object {
   *     __id => "0.43080500 1158148350"
   *   }
   * </xmp>
   *
   * @return  string
   */
  public function toString() {
    if (!$this->__id) $this->__id= uniqid('', true);
    return \xp::stringOf($this);
  }
}
