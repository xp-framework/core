<?php namespace lang;

/**
 * Throwable
 *
 * @see   xp://lang.Error
 * @see   xp://lang.XPException
 * @see   http://mindprod.com/jgloss/chainedexceptions.html
 * @see   http://www.jguru.com/faq/view.jsp?EID=1026405  
 * @test  xp://net.xp_framework.unittest.core.ExceptionsTest
 * @test  xp://net.xp_framework.unittest.core.ChainedExceptionTest
 */
class Throwable extends \Exception implements Value {
  public $__id;

  public 
    $cause    = null,
    $message  = '',
    $trace    = [];

  /**
   * Constructor
   *
   * @param  string $message
   * @param  lang.Throwable|php.Throwable|php.Exception $cause
   * @param  bool $fill Whether to populate stack trace
   */
  public function __construct($message, $cause= null, $fill= true) {
    $this->__id= uniqid('', true);
    $this->message= is_string($message) ? $message : \xp::stringOf($message);
    $cause && $this->cause= self::wrap($cause);
    $fill && $this->fillInStackTrace();
  }

  /**
   * Wraps an exception inside a throwable
   *
   * @param  lang.Throwable|php.Throwable|php.Exception $e
   * @return self
   * @throws lang.IllegalArgumentException
   */
  public static function wrap($e): self {
    if ($e instanceof self) {
      return $e;
    } else if ($e instanceof \Exception) {
      $wrapped= new XPException($e->getMessage(), $e->getPrevious(), false);
      $wrapped->addStackTraceFor($e->getFile(), '<native>', get_class($e), $e->getLine(), [$e->getCode(), $e->getMessage()], [['' => 1]]);
      $wrapped->fillInStackTrace($e);
      return $wrapped;
    } else if ($e instanceof \Throwable) {
      $wrapped= new Error($e->getMessage(), $e->getPrevious(), false);
      $wrapped->addStackTraceFor($e->getFile(), '<native>', get_class($e), $e->getLine(), [$e->getCode(), $e->getMessage()], [['' => 1]]);
      $wrapped->fillInStackTrace($e);
      return $wrapped;
    } else {
      throw new IllegalArgumentException('Given argument must be a lang.Throwable or a PHP base exception');
    }
  }

  /** Set cause */
  public function setCause(self $cause= null) { $this->cause= $cause; }

  /** @return self */
  public function getCause() { return $this->cause; }
  
  /**
   * Fills in stack trace information. 
   *
   * @param   self $from
   * @return  self this
   */
  public function fillInStackTrace($from= null) {
    static $except= [
      'call_user_func_array'  => 1, 
      'call_user_func'        => 1
    ];

    // Error messages
    foreach (\xp::$errors as $file => $list) {
      $this->addStackTraceFor($file, null, null, null, [], $list);
    }

    foreach ($from ? $from->getTrace() : $this->getTrace() as $i => $trace) {
      if (
        !isset($trace['function']) || 
        isset($except[$trace['function']]) ||
        (isset($trace['object']) && $trace['object'] instanceof self)
      ) continue;

      // Not all of these are always set: debug_backtrace() should
      // initialize these - at least - to NULL, IMO => Workaround.
      $this->addStackTraceFor(
        $trace['file'] ?? null,
        $trace['class'] ?? null,
        $trace['function'] ?? null,
        $trace['line'] ?? null,
        $trace['args'] ?? null,
        [['' => 1]]
      );
    }
    return $this;
  }
  
  /**
   * Adds new stacktrace elements to the internal list of stacktrace
   * elements, each for one error.
   *
   * @param   string file
   * @param   string class
   * @param   string function
   * @param   int originalline
   * @param   var[] args
   * @param   var[] errors
   */
  protected function addStackTraceFor($file, $class, $function, $originalline, $args, $errors) {
    foreach ($errors as $line => $errormsg) {
      foreach ($errormsg as $message => $details) {
        if (is_array($details)) {
          $class= $details['class'];
          $function= $details['method'];
          $amount= $details['cnt'];
        } else {
          $amount= $details;
        }
        
        $this->trace[]= new StackTraceElement(
          $file,
          $class,
          $function,
          $originalline ? $originalline : $line,
          $args,
          $message.($amount > 1 ? ' (... '.($amount - 1).' more)' : '')
        );
      }
    }
  }

  /**
   * Return an array of stack trace elements
   *
   * @return  lang.StackTraceElement[] array of stack trace elements
   * @see     xp://lang.StackTraceElement
   */
  public function getStackTrace() {
    return $this->trace;
  }

  /**
   * Print "stacktrace" to standard error
   *
   * @see     xp://lang.Throwable#toString
   * @param   resource fd default STDERR
   */
  public function printStackTrace($fd= STDERR) {
    fputs($fd, $this->toString());
  }

  /**
   * Return compound message of this exception. In this default 
   * implementation, returns the following:
   *
   * ```
   * Exception [FULLY-QUALIFIED-CLASSNAME] ([MESSAGE])
   * ```
   *
   * May be overriden by subclasses
   *
   * @return  string
   */
  public function compoundMessage() {
    return sprintf(
      'Exception %s (%s)',
      nameof($this),
      $this->message
    );
  }
 
  /**
   * Return compound message followed by the formatted output of this
   * exception's stacktrace.
   *
   * Example:
   * ```
   * Exception lang.ClassNotFoundException (class "" [] not found)
   *   at lang.ClassNotFoundException::__construct((0x15)'class "" [] not found') \
   *   [line 79 of StackTraceElement.class.php] 
   *   at lang.ClassLoader::loadclass(NULL) [line 143 of XPClass.class.php] 
   *   at lang.XPClass::forname(NULL) [line 6 of base_test.php] \
   *   Undefined variable:  nam
   * ```
   *
   * Usually not overridden by subclasses unless stacktrace format 
   * should differ - otherwise overwrite compoundMessage() instead!.
   */
  public function toString(): string {
    $s= $this->compoundMessage()."\n";
    $tt= $this->getStackTrace();
    $t= sizeof($tt);
    for ($i= 0; $i < $t; $i++) {
      $s.= $tt[$i]->toString(); 
    }
    if (!$this->cause) return $s;
    
    $loop= $this->cause;
    while ($loop) {

      // String of cause
      $s.= 'Caused by '.$loop->compoundMessage()."\n";

      // Find common stack trace elements
      $lt= $loop->getStackTrace();
      for ($ct= $cc= sizeof($lt)- 1, $t= sizeof($tt)- 1; $ct > 0 && $cc > 0 && $t > 0; $cc--, $t--) {
        if (0 !== $lt[$cc]->compareTo($tt[$t])) break;
      }

      // Output uncommon elements only and one line how many common elements exist!
      for ($i= 0; $i < $cc; $i++) {
        $s.= $lt[$i]->toString(); 
      }
      if ($cc != $ct) $s.= '  ... '.($ct - $cc + 1)." more\n";
      
      $loop= $loop->cause;
      $tt= $lt;
    }
    
    return $s;
  }

  /** Returns a hashcode for this object */
  public function hashCode(): string {
    return $this->__id;
  }
  
  /** Compares to another value */
  public function compareTo($value): int {
    return $value instanceof self ? $this->__id <=> $value->__id : 1;
  }
}
