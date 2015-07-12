<?php namespace text\format;

/**
 * Message formatter
 *
 * <code>
 *   $mf= new MessageFormat(
 *     '{2,date,%Y-%m-%d} The disk "{1}" contains {0,printf,%4d} file(s)'
 *   );
 *   
 *   Console::writeLine($mf->format(1282, 'MyDisk', Date::now()));
 *   Console::writeLine($mf->format(42, 'HomeDisk', Date::now()));
 * </code>
 *
 * <code>
 *   $mf= new MessageFormat(
 *     'The disk "{1}" contains {0,choice,0:no files|1:one file|*:{0,number,0#,#`} files}.'
 *   );
 *   
 *   $message= [];
 *   $message[]= $mf->format(1282, 'MyDisk');
 *   $message[]= $mf->format(1, 'MyDisk');
 *   $message[]= $mf->format(0, 'MyDisk');
 *   
 *   Console::writeLine($message);
 * </code>
 *
 * @deprecated
 */
class MessageFormat extends IFormat {
  public
    $formatters   = [];
    
  /**
   * Constructor
   *
   * @param   string f default NULL format string
   */
  public function __construct($f= null) {

    // Add some default formatters
    $this->setFormatter('printf', PrintfFormat::getInstance());
    $this->setFormatter('date',   DateFormat::getInstance());
    $this->setFormatter('choice', ChoiceFormat::getInstance());
    $this->setFormatter('number', NumberFormat::getInstance());
    $this->setFormatter('array',  ArrayFormat::getInstance());
    $this->setFormatter('hash',   HashFormat::getInstance());

    parent::__construct($f);
  }
  
  /**
   * Get an instance
   *
   * @return  text.format.MessageFormat
   */
  public function getInstance() {
    return parent::getInstance('MessageFormat');
  }

  /**
   * Set a format handler for a special type
   *
   * @param   string alias
   * @param   text.format.PrintfFormat formatter
   * @return  text.format.PrintfFormat formatter
   * @throws  lang.IllegalArgumentException 
   */
  public function setFormatter($alias, $formatter) {
    if (!$formatter instanceof IFormat) {
      throw new \lang\IllegalArgumentException('Formatter must be a text.format.Format');
    }
    $this->formatters[$alias]= $formatter;
    return $this->formatters[$alias];
  }
  
  /**
   * Check whether a given formatter exists
   *
   * @param   string alias
   * @return  bool true in case the specified formatter exists, false otherwise
   */
  public function hasFormatter($alias) {
    return isset($this->formatters[$alias]);
  }
  
  /**
   * Apply format to argument
   *
   * @param   var fmt
   * @param   var argument
   * @return  string
   */
  public function apply($fmt, $argument) {
    static $instance;
    static $level= 0;
    
    if (false === ($p= strpos($fmt, '{'))) return $fmt;
    if (!isset($instance)) {
      $instance= MessageFormat::getInstance();
    }
    if (!is_array($argument)) $argument= [$argument];
    $level++;
    
    // Loop while {'s can be found
    $result= '';
    do {
      $result.= substr($fmt, 0, $p);

      // Find corresponding closing bracket
      $index= $rest= false;
      $c= 0;
      for ($i= $p, $l= strlen($fmt); $i < $l; $i++) {
        switch ($fmt{$i}) {
          case '{': $c++; break;
          case '}': 
            if (0 >= --$c) {
              $index= substr($fmt, $p+ 1, $i- $p- 1);
              $fmt= substr($fmt, $i+ 1);
              break 2; 
            }
            break;
        }
      }
      
      // No closing bracket found
      if (false === $index) {
        trigger_error(sprintf(
          'Opening bracket found at position %d of "%s"',
          $p,
          $fmt
        ), E_USER_NOTICE);
        throw new \lang\FormatException('Parse error [level '.$level.']: closing curly bracket not found');
      }
      
      // Syntax: {2} = paste argument, {2,printf,%s} use formatter
      if (false !== strpos($index, ',')) {
        list($index, $type, $param)= explode(',', $index, 3);
      } else {
        $type= $param= null;
      }
      
      // Check argument index
      if (!isset($argument[$index])) {
        throw new \lang\FormatException('Missing argument at index '.$index);
      }
      
      // Default
      if (null == $type) {
        $result.= (is_object($argument[$index]) && method_exists($argument[$index], 'toString')
          ? $argument[$index]->toString()
          : $argument[$index]
        );
        continue;
      }
      
      // No formatter registered
      if (!$this->hasFormatter($type)) {
        throw new \lang\FormatException('Unknown formatter "'.$type.'"');
      }
      
      // Formatters return FALSE to indicate failure
      if (false === ($format= $this->formatters[$type]->apply($param, $argument[$index]))) {
        return false;
      }
      
      // Look to see if a formatstring was returned
      if (false !== strpos($format, '{')) {
        $format= $instance->apply($format, $argument[$index]);
      }
      
      // Append formatter's result
      $result.= $format;
    } while (false !== ($p= strpos($fmt, '{')));
    
    return $result.$fmt;    
  }
}
