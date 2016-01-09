<?php namespace util\cmd;
 
/**
 * This class provides helpful functions for commandline applications
 * to parse the argument list
 *
 * It supports short and long options, e.g. -h or --help
 *
 * @deprecated  See https://github.com/xp-framework/rfc/issues/307
 * @test   xp://net.xp_framework.unittest.util.cmd.ParamStringTest
 */
class ParamString extends \lang\Object {
  public 
    $list     = [],
    $count    = 0,
    $string   = '';
  
  /**
   * Constructor
   * 
   * @param   array list default NULL the argv array. If omitted, $_SERVER['argv'] is used
   */
  public function __construct($list= null) {
    $this->setParams(null === $list ? $_SERVER['argv'] : $list);
  }
  
  /**
   * Set the parameter string
   * 
   * @param   array params
   */  
  public function setParams($params) {
    $this->list= $params;
    $this->list[-1]= @$_SERVER['_'];
    $this->count= sizeof($params);
    $this->string= implode(' ', $params);
  }
  
  /**
   * Private helper function that iterates through the parameter array
   * 
   * @param   string long long parameter (w/o --)
   * @param   string short default NULL Short parameter (w/o -), defaults to the first char of the long param
   * @return  var position on which the parameter is placed or FALSE if nonexistant
   */ 
  protected function _find($long, $short= null) {
    if (is_null($short)) $short= $long{0};
    foreach (array_keys($this->list) as $i) {
    
      // Short notation (e.g. -f value)
      if ($this->list[$i] == '-'.$short) return $i+ 1;
      
      // Long notation (e.g. --help, without a value)
      if ($this->list[$i] == '--'.$long) return $i;
      
      // Long notation (e.g. --file=*.txt)
      if (substr($this->list[$i], 0, strlen($long)+ 3) == '--'.$long.'=') return $i;
    }
    
    return false;
  }
 
  /**
   * Checks whether a parameter is set
   * 
   * @see     xp://util.Properties#value
   * @param   string long long parameter (w/o --)
   * @param   string short default NULL Short parameter (w/o -), defaults to the first char of the long param
   * @return  bool
   */  
  public function exists($long, $short= null) {
    if (is_int($long)) return isset($this->list[$long]);
    return ($this->_find($long, $short) !== false);
  }
  
  /**
   * Retrieve the value of a given parameter
   *
   * Examples:
   * <code>
   *   $p= new ParamString();
   *   if ($p->exists('help', '?')) {
   *     printf("Usage: %s %s --force-check [--pattern={pattern}]\n", $p->value(-1), $p->value(0));
   *     exit(-2);
   *   }
   * 
   *   $force= $p->exists('force-check', 'f');
   *   $pattern= $p->value('pattern', 'p', '.*');
   * 
   *   // ...
   * </code>
   * 
   * @param   string long long parameter (w/o --)
   * @param   string short default NULL Short parameter (w/o -), defaults to the first char of the long param
   * @param   string default default NULL A default value if parameter does not exist
   * @return  string 
   * @throws  lang.IllegalArgumentException if parameter does not exist and no default value was supplied.
   */ 
  public function value($long, $short= null, $default= null) {
    if (is_int($long)) {
      if (null === $default && !isset($this->list[$long])) {
        throw new \lang\IllegalArgumentException ('Parameter #'.$long.' does not exist');
      }

      return isset($this->list[$long]) ? $this->list[$long] : $default;
    }

    $pos= $this->_find($long, $short);
    if (false === $pos && null === $default) {
      throw new \lang\IllegalArgumentException ('Parameter --'.$long.' does not exist');
    }
    
    // Default usage (eg.: '--with-foo=bar')
    $length= strlen($long)+ 2;
    if ($pos !== false && isset($this->list[$pos]) && strncmp('--'.$long, $this->list[$pos], $length) == 0) {
    
      // Usage with value (eg.: '--with-foo=bar')
      if (strlen($this->list[$pos]) > $length && '=' === $this->list[$pos]{$length}) {
        return substr($this->list[$pos], $length + 1);  // Return string after `--{option}=`
      }
        
      // Usage as switch (eg.: '--enable-foo')
      return null;
    }
    
    // Usage in short (eg.: '-v' or '-f /foo/bar')
    // If the found element is a new parameter, the searched one is used as
    // flag, so just return TRUE, otherwise return the value.
    if ($pos !== false && (!isset($this->list[$pos]) || '-' === $this->list[$pos]{0})) {
      return $default;
    }
    
    return ($pos !== false ? $this->list[$pos] : $default);
  }
}
