<?php namespace io\sys;

use io\File;

/**
 * Wrap standard I/O streams with File objects
 *
 * @deprecated Use io.streams APIs instead
 * @see   http://www.opengroup.org/onlinepubs/007908799/xsh/stdin.html
 */
class StdStream extends \lang\Object {

  /**
   * Retrieve a file object
   *
   * <code>
   *   $stdout= StdStream::get(STDOUT);
   *   $stdout->write('Hello');
   * </code>
   *
   * @param   resource handle one of STDIN | STDOUT | STDERR
   * @return  io.File
   */
  public static function get($handle) {
    static $f= [];
    
    if (!isset($f[$handle])) {
      $f[$handle]= new File($handle);
    }
    return $f[$handle];
  }
}
