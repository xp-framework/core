<?php namespace xp\xar;

use io\File;
use lang\archive\Archive;
use lang\reflect\Package;
use util\cmd\Console;
use xp\runtime\Help;

/** XAR command */
class Runner {

  /**
   * Set operation
   *
   * @param   xp.xar.instruction.AbstractInstruction operation
   * @param   string name
   */
  protected static function setOperation(&$operation, $name) {
    if (null !== $operation) {
      self::bail('Cannot execute more than one instruction at a time.');
    }
    $operation= Package::forName('xp.xar.instruction')->loadClass(ucfirst($name).'Instruction');
  }
  
  /**
   * Displays a message on STDERR
   *
   * @return  int
   */
  protected static function bail($message) {
    Console::$err->writeLine('*** ', $message);
    return 1;
  }

  /**
   * Displays usage
   *
   * @return  int
   */
  protected static function usage() {
    return Help::main(['@xp/runtime/ar.md']);
  }

  /**
   * Main runner method
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    if (!$args) return self::usage();
    
    // Parse command line
    $operation= null;
    $std= 'php://stdin';
    for ($i= 0; $i < sizeof($args); $i++) {
      if ('-R' == $args[$i]) {
        chdir($args[++$i]);
      } else if (in_array($args[$i], ['-?', '-h', '--help'])) {
        return self::usage();
      } else {
        $options= 0;
        $offset= $i;
        for ($o= 0; $o < strlen($args[$i]); $o++) {
          switch ($args[$i]{$o}) {
            case 'c': 
              self::setOperation($operation, 'create'); 
              $std= 'php://stdout';
              break;
            case 'x': 
              self::setOperation($operation, 'extract'); 
              break;
            case 's': 
              self::setOperation($operation, 'show'); 
              break;
            case 'd': 
              self::setOperation($operation, 'diff'); 
              break;
            case 't':
              self::setOperation($operation, 'extract');
              $options |= Options::SIMULATE | Options::VERBOSE;
              break;
            case 'm':
              self::setOperation($operation, 'merge');
              $std= 'php://stdout';
              break;
            case 'v': 
              $options |= Options::VERBOSE; 
              break;
            case 'f': 
              $file= new File($args[$i+ 1]);
              $std= null;
              $offset++;
              break;
            default: 
              return self::bail('Unsupported commandline option "'.$args[$i].'"');
          }
        }
        $args= array_slice($args, $offset+ 1);
        break;
      }
    }
    
    if (!$operation) return self::usage();
    
    // Use STDOUT & STDERR if no file is given
    if ($std) $file= new File($std);
   
    // Perform operation
    $operation->newInstance(Console::$out, Console::$err, $options, new Archive($file), $args)->perform();
  } 
}
