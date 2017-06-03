<?php namespace io\sys;
 
/**
 * Semaphore
 *
 * <code>
 *   $s= Semaphore::get(6100);
 *   $s->acquire();
 *   // [...]
 *   $s->release();
 *   $s->remove();
 * </code>
 *
 * @ext   sem
 * @see   http://www.cs.cf.ac.uk/Dave/C/node27.html#SECTION002700000000000000000
 * @see   http://www.cs.cf.ac.uk/Dave/C/node26.html#SECTION002600000000000000000
 */
class Semaphore {
  public
    $key       = 0,
    $maxAquire = 1;
    
  public
    $_hdl      = null;
    
  /**
   * Get a semaphore
   *
   * Note: A second call to this function with the same key will actually return
   * the same semaphore
   *
   * @param   int key
   * @param   int maxAquire default 1
   * @param   int permissions default 0666
   * @return  io.sys.Semaphore a semaphore
   * @throws  io.IOException
   */
  public static function get($key, $maxAquire= 1, $permissions= 0666) {
    static $semaphores= [];
    
    if (!isset($semaphores[$key])) {
      $s= new self();
      $s->key= $key;
      $s->maxAquire= $maxAquire;
      $s->permissions= $permissions;
      if (false === ($s->_hdl= sem_get($key, $maxAquire, $permissions, true))) {
        throw new \io\IOException('Could not get semaphore '.$key);
      }
      
      $semaphores[$key]= $s;
    }
    
    return $semaphores[$key];
  }
  
  /**
   * Acquire a semaphore - blocks (if necessary) until the semaphore can be acquired. 
   * A process attempting to acquire a semaphore which it has already acquired will 
   * block forever if acquiring the semaphore would cause its max_acquire value to 
   * be exceeded. 
   *
   * @return  bool success
   * @throws  io.IOException
   */
  public function acquire() {
    if (false === sem_acquire($this->_hdl)) {
      throw new \io\IOException('Could not acquire semaphore '.$this->key);
    }
    return true;
  }
  
  /**
   * Release a semaphore
   * After releasing the semaphore, acquire() may be called to re-acquire it. 
   *
   * @return  bool success
   * @throws  io.IOException
   * @see     xp://io.sys.Semaphore#acquire
   */
  public function release() {
    if (false === sem_release($this->_hdl)) {
      throw new \io\IOException('Could not release semaphore '.$this->key);
    }
    return true;
  }
  
  /**
   * Remove a semaphore
   * After removing the semaphore, it is no more accessible.
   *
   * @return  bool success
   * @throws  io.IOException
   */
  public function remove() {
    if (false === sem_remove($this->_hdl)) {
      throw new \io\IOException('Could not remove semaphore '.$this->key);
    }
    return true;
  }
}
