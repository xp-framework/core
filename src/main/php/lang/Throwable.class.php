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
class Throwable extends \Exception implements Generic { use \__xp;
  public $__id;

  public 
    $cause    = null,
    $message  = '',
    $trace    = [];
  
  static function __static() {
  
    // Workaround for missing detail information about return types in
    // builtin classes.
    \xp::$meta['php.Exception']= [
      'class' => [4 => null, []],
      0 => [],
      1 => [
        'getMessage'       => [1 => [], 'string', [], null, []],
        'getCode'          => [1 => [], 'int', [], null, []],
        'getFile'          => [1 => [], 'string', [], null, []],
        'getLine'          => [1 => [], 'int', [], null, []],
        'getTrace'         => [1 => [], 'var[]', [], null, []],
        'getPrevious'      => [1 => [], 'lang.Throwable', [], null, []],
        'getTraceAsString' => [1 => [], 'string', [], null, []]
      ]
    ];
  }

  /**
   * Constructor
   *
   * @param   string message
   * @param   lang.Throwable cause default null
   */
  public function __construct($message, self $cause= null) {
    $this->__id= uniqid('', true);
    $this->message= is_string($message) ? $message : \xp::stringOf($message);
    $this->cause= $cause;
    $this->trace= [];
    $this->fillInStackTrace();
  }

  /**
   * Set cause
   *
   * @param   lang.Throwable cause
   */
  public function setCause($cause) {
    $this->cause= $cause;
  }

  /**
   * Get cause
   *
   * @return  lang.Throwable
   */
  public function getCause() {
    return $this->cause;
  }
  
  /**
   * Fills in stack trace information. 
   *
   * @return  lang.Throwable this
   */
  public function fillInStackTrace() {
    static $except= [
      'call_user_func_array'  => 1, 
      'call_user_func'        => 1
    ];

    // Error messages
    foreach (\xp::$errors as $file => $list) {
      $this->addStackTraceFor($file, null, null, null, [], $list);
    }

    foreach (debug_backtrace() as $i => $trace) {
      if (
        !isset($trace['function']) || 
        isset($except[$trace['function']]) ||
        (isset($trace['object']) && $trace['object'] instanceof self)
      ) continue;

      // Not all of these are always set: debug_backtrace() should
      // initialize these - at least - to NULL, IMO => Workaround.
      $this->addStackTraceFor(
        isset($trace['file']) ? $trace['file'] : null,
        isset($trace['class']) ? $trace['class'] : null,
        isset($trace['function']) ? $trace['function'] : null,
        isset($trace['line']) ? $trace['line'] : null,
        isset($trace['args']) ? $trace['args'] : null,
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
   * <pre>
   *   Exception [FULLY-QUALIFIED-CLASSNAME] ([MESSAGE])
   * </pre>
   *
   * May be overriden by subclasses
   *
   * @return  string
   */
  public function compoundMessage() {
    return sprintf(
      'Exception %s (%s)',
      $this->getClassName(),
      $this->message
    );
  }
 
  /**
   * Return compound message followed by the formatted output of this
   * exception's stacktrace.
   *
   * Example:
   * <pre>
   * Exception lang.ClassNotFoundException (class "" [] not found)
   *   at lang.ClassNotFoundException::__construct((0x15)'class "" [] not found') \
   *   [line 79 of StackTraceElement.class.php] 
   *   at lang.ClassLoader::loadclass(NULL) [line 143 of XPClass.class.php] 
   *   at lang.XPClass::forname(NULL) [line 6 of base_test.php] \
   *   Undefined variable:  nam
   * </pre>
   *
   * Usually not overridden by subclasses unless stacktrace format 
   * should differ - otherwise overwrite compoundMessage() instead!.
   *
   * @return  string
   */
  public function toString() {
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
        if (!$lt[$cc]->equals($tt[$t])) break;
      }

      // Output uncommon elements only and one line how many common elements exist!
      for ($i= 0; $i < $cc; $i++) {
        $s.= \xp::stringOf($lt[$i]); 
      }
      if ($cc != $ct) $s.= '  ... '.($ct - $cc + 1)." more\n";
      
      $loop= $loop->cause;
      $tt= $lt;
    }
    
    return $s;
  }

  /**
   * Returns a hashcode for this object
   *
   * @return  string
   */
  public function hashCode() {
    return $this->__id;
  }
  
  /**
   * Indicates whether some other object is "equal to" this one.
   *
   * @param   lang.Generic cmp
   * @return  bool TRUE if the compared object is equal to this object
   */
  public function equals($cmp) {
    if (!$cmp instanceof Generic) return false;
    if (!$cmp->__id) $cmp->__id= uniqid('', true);
    return $this === $cmp;
  }
  
  /** 
   * Returns the fully qualified class name for this class 
   * (e.g. "io.File")
   * 
   * @return  string fully qualified class name
   */
  public function getClassName() {
    return \xp::nameOf(get_class($this));
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
   * Returns the runtime class of a class.
   *
   * @return  lang.XPClass runtime class
   * @see     xp://lang.XPClass
   */
  public static function type() {
    return new XPClass(get_called_class());
  }
}
