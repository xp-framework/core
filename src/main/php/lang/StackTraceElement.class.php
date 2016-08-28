<?php namespace lang;

/**
 * An element in a stack trace, as returned by Throwable::getStackTrace(). 
 * Each element represents a single stack frame.
 *
 * @see   xp://lang.Throwable#getStackTrace
 * @test  xp://net.xp_framework.unittest.core.ExceptionsTest
 * @test  xp://net.xp_framework.unittest.core.StackTraceElementTest
 */
class StackTraceElement extends Object {
  public
    $file     = '',
    $class    = '',
    $method   = '',
    $line     = 0,
    $args     = [],
    $message  = '';
    
  /**
   * Constructor
   *
   * @param  string $file
   * @param  string $class
   * @param  string $method
   * @param  int $line
   * @param  var[] $args
   * @param  string $message
   */
  public function __construct($file, $class, $method, $line, $args, $message) {
    $this->file     = $file;  
    $this->class    = $class; 
    $this->method   = $method;
    $this->line     = $line;
    $this->args     = $args;
    $this->message  = $message;
  }
  
  /** Create string representation */
  public function toString(): string {
    $args= [];
    if (isset($this->args)) {
      foreach ($this->args as $arg) {
        if (is_array($arg)) {
          $args[]= 'array['.sizeof($arg).']';
        } else if ($arg instanceof \Closure) {
          $args[]= \xp::stringOf($arg);
        } else if (is_object($arg)) {
          $args[]= nameof($arg).'{}';
        } else if (is_string($arg)) {
          $display= str_replace('%', '%%', addcslashes(substr($arg, 0, min(
            (false === $p= strpos($arg, "\n")) ? 0x40 : $p,
            0x40
          )), "\0..\17"));
          $args[]= '(0x'.dechex(strlen($arg)).")'".$display."'";
        } else if (null === $arg) {
          $args[]= 'NULL';
        } else if (is_scalar($arg)) {
          $args[]= (string)$arg;
        } else if (is_resource($arg)) {
          $args[]= (string)$arg;
        } else {
          $args[]= '<'.gettype($arg).'>';
        }
      }
    }
    return sprintf(
      "  at %s::%s(%s) [line %d of %s] %s\n",
      isset($this->class) ? XPClass::nameOf($this->class) : '<main>',
      $this->method ?? '<main>',
      implode(', ', $args),
      $this->line,
      basename($this->file ?? __FILE__),
      $this->message
    );
  }

  /** Compares this stacktrace element to another object */
  public function equals($cmp): bool {
    return $cmp instanceof self && $this->toString() === $cmp->toString();
  }
}
