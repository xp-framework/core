<?php namespace io\sys;

use io\IOException;
 
/**
 * Shared memory segment
 *
 * Shared memory may be used to provide access to global variables. Different 
 * httpd-daemons and even other programs (such as Perl, C, ...) are able to access 
 * this data to provide a global data-exchange. Remember, that shared memory is NOT 
 * safe against simultaneous access. Use semaphores for synchronization.
 *
 * Note: This extension is not available on Windows platforms. 
 *
 * @deprecated See https://github.com/xp-framework/rfc/issues/329
 * @ext   sem
 * @see   http://www.cs.cf.ac.uk/Dave/C/node27.html#SECTION002700000000000000000
 * @see   http://www.cs.cf.ac.uk/Dave/C/node26.html#SECTION002600000000000000000
 * @see   xp://io.sys.Semaphore
 */
class ShmSegment {
  public 
    $name     = '',
    $spot     = '';
    
  /**
   * Constructor
   *
   * @param   string name
   */
  public function __construct($name) {
    $this->name= $name;
    $str= str_pad($name, 4, 'Z');
    $this->spot= '';
    for ($i= 0; $i < 4; $i++) {
      $this->spot.= dechex(ord($str[$i]));
    }
    $this->spot= hexdec('0x'.$this->spot);
    
  }
  
  /**
   * Private helper function
   *
   * @return  var data
   */
  protected function _get() {
    $h= shm_attach($this->spot);
    $data= shm_get_var($h, $this->name);
    shm_detach($h);
    
    return is_array($data) ? $data : false;
  }
  
  /**
   * Returns whether this segment is empty (i.e., has not been written or was
   * previously removed)
   *
   * @return  bool TRUE if this segment is empty
   */
  public function isEmpty() {
    return (false === $this->_get());
  }
  
  /**
   * Get this segment's contents
   *
   * @return  var data
   * @throws  io.IOException in case an error occurs
   */
  public function get() {
    if (false === ($data= $this->_get())) {
      throw new IOException('Could not read segment '.$this->name);
    }
    
    return $data[0];
  }

  /**
   * Put this segment's contents
   *
   * @param   var data
   * @param   int permissions default 0666 permissions
   * @return  bool success
   * @throws  io.IOException in case an error occurs
   */
  public function put($val, $permissions= 0666) {
    $v= [$val];
    $h= shm_attach($this->spot, (strlen(serialize($v)) + 44) * 2, $permissions);
    $ret= shm_put_var($h, $this->name, $v);
    shm_detach($h);
    if (false === $ret) {
      throw new IOException('Could not write segment '.$this->name);
    }
    
    return $ret;
  }
  
  /**
   * Remove this segment's contents
   *
   * @return  bool success
   * @throws  io.IOException in case an error occurs
   */
  public function remove() {
    $h= shm_attach($this->spot);
    $ret= shm_remove_var($h, $this->name);
    shm_detach($h);
    
    if (false === $ret) {
      throw new IOException('Could not remove segment '.$this->name);
    }
    
    return $ret;
  }
}