<?php
/* Diese Klasse ist Teil des XP-Frameworks
 *
 * $Id$
 */

  uses(
    'xml.Node',
    'xml.soap.types.SOAPBase64Binary',
    'xml.soap.types.SOAPDateTime',
    'xml.soap.types.SOAPNamedItem'
  );

  /**
   * SOAP Node
   *
   * @see   xp://xml.Node
   */
  class SOAPNode extends Node {
    var 
      $namespace= 'ctl';
    
    /**
     * Get type name by content
     *
     * @access  private
     * @param   &mixed content
     * @return  string typename, e.g. "xsd:string"
     */
    function _typeName(&$content) {
      static $tmap= array(      // Mapping PHP-typename => SOAP-typename
        'double'        => 'float',
        'integer'       => 'int'
      );
      
      $t= gettype($content);
      if (isset($tmap[$t])) $t= $tmap[$t];
      return 'xsd:'.$t;
    }
    
    /**
     * Format content
     *
     * @access  private
     * @param   &mixed content
     * @return  &mixed content, formatted, if necessary
     */
    function &_contentFormat(&$content) {
      switch (gettype($content)) {
        case 'boolean': return $content ? 'true' : 'false';
        case 'string': return htmlspecialchars($content);
      }
      return $content;
    }
    
    /**
     * Get content in iso-8859-1 encoding (the default).
     *
     * @access  public
     * @param   string encoding default NULL
     * @return  &mixed data
     */
    function &getContent($encoding= NULL) {
      static $tmap= array(      // Mapping SOAP-typename => PHP-typename
        'float'     => 'double',
        'int'       => 'integer'
      );

      $ret= $this->content;
      @list($ns, $t)= explode(':', @$this->attribute['xsi:type']);
      
      switch ($t) {
        case 'base64':
        case 'base64Binary':
          return new SOAPBase64Binary(base64_decode($ret));
          break;
        
        case 'boolean':
          return $ret == 'false' ? FALSE : TRUE;
         
        case 'long':
          $t= 'int';
          break;
          
        case 'date':
        case 'dateTime':    // ISO 8601: http://www.w3.org/TR/xmlschema-2/#ISO8601 http://www.w3.org/TR/xmlschema-2/#dateTime
          return new Date(str_replace('T', ' ', $ret));
          break;
      }        
      if (isset($tmap[$t])) $t= $tmap[$t];

      // Decode if necessary
      switch (strtolower($encoding)) {
        case 'utf-8': $ret= utf8_decode($ret); break;
      }

      // Rip HTML entities
      $ret= strtr($ret, array_flip(get_html_translation_table(HTML_ENTITIES)));

      // Set type
      if (!@settype($ret, $t)) settype($ret, 'string');         // Default "string"

      return $ret; 
    }
    
    /**
     * Recurse an array
     *
     * @access  private
     * @param   &mixed elemt
     * @param   array arr
     */
    function _recurseArray(&$elem, $arr) {
      static $ns;

      $nodeType= get_class($this);
      if (!is_array($arr)) return;
      foreach ($arr as $field=> $value) {
      
        // Private Variablen
        if ('_' == $field{0}) continue;
        
        $child= &$elem->addChild(new $nodeType(array(
          'name'        => (is_numeric($field) ? preg_replace('=s$=', '', $elem->name) : $field)
        )));
        unset($type);
       
        if (is_a($value, 'Date')) $value= &new SOAPDateTime($value->_utime);
        
        if (
          (is_object($value)) && 
          (is_a($value, 'SoapType'))
        ) {       
          // SOAP-Types
          
          if (FALSE !== ($name= $value->getItemName())) $child->name= $name;
          if (isset($value->item)) $child= $value->item;
          
          // Inhalt und Typen
          $content= $value->toString();
          $type= $value->getType();
          if (NULL === $type) {
            $type= $child->_typeName($content);
            $content= $child->_contentFormat($content);
          }
        } else if (is_object($value)) {
        
          // Objekte
          $content= get_object_vars($value);
          $ns++;
          $type= 'ns'.$ns.':struct';
          
          // Class name
          if (method_exists($value, 'getClassName')) {
            $name= $value->getClassName();
            $namespace= 'xp';
          } else {
            $name= get_class($value);
            $namespace= 'ns'.$ns;
          }
          
          $child->attribute['xmlns:'.$namespace]= $name;
        } else if (is_scalar($value)) {
        
          // Skalare Typen
          $type= $child->_typeName($value);
          $content= $child->_contentFormat($value);
	    } else if (NULL === $value) {

	      // NULL
	      $type= NULL;
	      $content= '';
	      $child->attribute['xsi:nil']= 'true';
	  
        } else {
        
          // Arrays
          $content= &$value;
        }

        // Arrays
        if (is_array($content)) {
          $this->_recurseArray($child, $content);
          if (isset($type)) {
            $child->attribute['xsi:type']= $type;
          } else {
            if (is_numeric(key($value))) {
              $child->attribute['xsi:type']= 'SOAP-ENC:Array';
              $child->attribute['SOAP-ENC:arrayType']= 'xsd:anyType['.sizeof($value).']';
            } else {
              $child->attribute['xsi:type']= 'xsd:ur-type';
            }
          }
          continue;
        }

        // Skalare Datentypen
        if (NULL !== $type) $child->attribute['xsi:type']= $type;
        $child->setContent($content);
      }
    }

  }
?>
