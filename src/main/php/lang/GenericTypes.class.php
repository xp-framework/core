<?php namespace lang;

/**
 * Generate generic runtime types.
 */
class GenericTypes extends \lang\Object {

  /**
   * Creates a generic type
   *
   * @param   lang.XPClass base
   * @param   lang.Type[] arguments
   * @return  lang.XPClass created type
   */
  public function newType(XPClass $base, array $arguments) {
    return new XPClass(new \ReflectionClass($this->newType0($base, $arguments)));
  }

  /**
   * Creates a generic type
   *
   * @param   lang.XPClass base
   * @param   lang.Type[] arguments
   * @return  string created type's literal
   */
  public function newType0($base, $arguments) {

    // Verify
    $annotations= $base->getAnnotations();
    if (!isset($annotations['generic']['self'])) {
      throw new IllegalStateException('Class '.$base->name.' is not a generic definition');
    }
    $components= array();
    foreach (explode(',', $annotations['generic']['self']) as $cs => $name) {
      $components[]= ltrim($name);
    }
    $cs++;
    if ($cs !== sizeof($arguments)) {
      throw new IllegalArgumentException(sprintf(
        'Class %s expects %d component(s) <%s>, %d argument(s) given',
        $base->name,
        $cs,
        implode(', ', $components),
        sizeof($arguments)
      ));
    }
  
    // Compose names
    $cn= $qc= '';
    foreach ($arguments as $typearg) {
      $cn.= '�'.strtr($typearg->literal(), '\\', '�');
      $qc.= ','.$typearg->getName();
    }
    $name= $base->literal().'��'.substr($cn, 1);
    $qname= $base->name.'<'.substr($qc, 1).'>';

    // Create class if it doesn't exist yet
    if (!class_exists($name, false) && !interface_exists($name, false)) {
      $meta= \xp::$meta[$base->name];

      // Parse placeholders into a lookup map
      $placeholders= array();
      foreach ($components as $i => $component) {
        $placeholders[$component]= $arguments[$i]->getName();
      }

      // Work on sourcecode
      $cl= $base->getClassLoader();
      if (!$cl || !($bytes= $cl->loadClassBytes($base->name))) {
        throw new IllegalStateException($base->name);
      }

      // Namespaced class
      if (false !== ($ns= strrpos($name, '\\'))) {
        $decl= substr($name, $ns + 1);
        $namespace= substr($name, 0, $ns);
        $src= 'namespace '.$namespace.';';
      } else {
        $decl= $name;
        $namespace= null;
        $src= '';
      }

      // Replace source
      $annotation= null;
      $matches= array();
      $state= array(0);
      $counter= 0;
      $tokens= token_get_all($bytes);
      for ($i= 0, $s= sizeof($tokens); $i < $s; $i++) {
        if (T_COMMENT === $tokens[$i][0]) {
          continue;
        } else if (0 === $state[0]) {
          if (T_ABSTRACT === $tokens[$i][0] || T_FINAL === $tokens[$i][0]) {
            $src.= $tokens[$i][1].' ';
          } else if (T_CLASS === $tokens[$i][0] || T_INTERFACE === $tokens[$i][0]) {
            $meta['class'][DETAIL_GENERIC]= array($base->name, $arguments);
            $src.= $tokens[$i][1].' '.$decl;
            array_unshift($state, $tokens[$i][0]);
          }
          continue;
        } else if (T_CLASS === $state[0]) {
          if (T_EXTENDS === $tokens[$i][0]) {
            $i+= 2;
            $parent= '';
            while ((T_STRING === $tokens[$i][0] || T_NS_SEPARATOR === $tokens[$i][0]) && $i < $s) {
              $parent.= $tokens[$i][1];
              $i++;
            }
            $i--;
            '\\' === $parent{0} || $parent= $namespace.'\\'.$parent;
            if (isset($annotations['generic']['parent'])) {
              $xargs= array();
              foreach (explode(',', $annotations['generic']['parent']) as $j => $placeholder) {
                $xargs[]= Type::forName(strtr(ltrim($placeholder), $placeholders));
              }
              $src.= ' extends \\'.$this->newType0($base->getParentClass(), $xargs);
            } else {
              $src.= ' extends '.$parent;
            }
          } else if (T_IMPLEMENTS === $tokens[$i][0]) {
            $src.= ' implements';
            $counter= 0;
            $annotation= @$annotations['generic']['implements'];
            array_unshift($state, 5);
          } else if ('{' === $tokens[$i][0]) {
            array_shift($state);
            array_unshift($state, 1);
            $src.= ' {';
          }
          continue;
        } else if (T_INTERFACE === $state[0]) {
          if (T_EXTENDS === $tokens[$i][0]) {
            $src.= ' extends';
            $counter= 0;
            $annotation= @$annotations['generic']['extends'];
            array_unshift($state, 5);
          } else if ('{' === $tokens[$i][0]) {
            array_shift($state);
            array_unshift($state, 1);
            $src.= ' {';
          }
          continue;
        } else if (1 === $state[0]) {             // Class body
          if (T_FUNCTION === $tokens[$i][0]) {
            $braces= 0;
            $parameters= $default= array();
            array_unshift($state, 3);
            array_unshift($state, 2);
            $m= $tokens[$i+ 2][1];
            $p= 0;
            $annotations= array($meta[1][$m][DETAIL_ANNOTATIONS], $meta[1][$m][DETAIL_TARGET_ANNO]);
          } else if ('}' === $tokens[$i][0]) {
            $src.= '}';
            break;
          } else if (T_CLOSE_TAG === $tokens[$i][0]) {
            break;
          }
        } else if (2 === $state[0]) {             // Method declaration
          if ('(' === $tokens[$i][0]) {
            $braces++;
          } else if (')' === $tokens[$i][0]) {
            $braces--;
            if (0 === $braces) {
              array_shift($state);
              $src.= ')';
              continue;
            }
          }
          if (T_VARIABLE === $tokens[$i][0]) {
            $parameters[]= $tokens[$i][1];
          } else if ('=' === $tokens[$i][0]) {
            $p= sizeof($parameters)- 1;
            $default[$p]= '';
          } else if (T_WHITESPACE !== $tokens[$i][0] && isset($default[$p])) {
            $default[$p].= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
          }
        } else if (3 === $state[0]) {             // Method body
          if (';' === $tokens[$i][0]) {
            // Abstract method
            if (isset($annotations[0]['generic']['return'])) {
              $meta[1][$m][DETAIL_RETURNS]= strtr($annotations[0]['generic']['return'], $placeholders);
            }
            if (isset($annotations[0]['generic']['params'])) {
              foreach (explode(',', $annotations[0]['generic']['params']) as $j => $placeholder) {
                if ('' !== ($replaced= strtr(ltrim($placeholder), $placeholders))) {
                  $meta[1][$m][DETAIL_ARGUMENTS][$j]= $replaced;
                }
              }
            }
            $annotations= array();
            unset($meta[1][$m][DETAIL_ANNOTATIONS]['generic']);
            array_shift($state);
          } else if ('{' === $tokens[$i][0]) {
            $braces= 1;
            array_shift($state);
            array_unshift($state, 4);
            $src.= '{';
            
            if (isset($annotations[0]['generic']['return'])) {
              $meta[1][$m][DETAIL_RETURNS]= strtr($annotations[0]['generic']['return'], $placeholders);
            }
            if (isset($annotations[0]['generic']['params'])) {
              $generic= array();
              foreach (explode(',', $annotations[0]['generic']['params']) as $j => $placeholder) {
                if ('' === ($replaced= strtr(ltrim($placeholder), $placeholders))) {
                  $generic[$j]= null;
                } else {
                  $meta[1][$m][DETAIL_ARGUMENTS][$j]= $replaced;
                  $generic[$j]= $replaced;
                }
              }
              foreach ($generic as $j => $type) {
                if (null === $type) {
                  continue;
                } else if ('...' === substr($type, -3)) {
                  $src.= $j ? '$�args= array_slice(func_get_args(), '.$j.');' : '$�args= func_get_args();';
                  $src.= (
                    ' if (!is(\''.substr($generic[$j], 0, -3).'[]\', $�args)) throw new \lang\IllegalArgumentException('.
                    '"Vararg '.($j + 1).' passed to ".__METHOD__."'.
                    ' must be of '.$type.', ".\xp::stringOf($�args)." given"'.
                    ');'
                  );
                } else {
                  $src.= (
                    ' if ('.(isset($default[$j]) ? '('.$default[$j].' !== '.$parameters[$j].') && ' : '').
                    '!is(\''.$generic[$j].'\', '.$parameters[$j].')) throw new \lang\IllegalArgumentException('.
                    '"Argument '.($j + 1).' passed to ".__METHOD__."'.
                    ' must be of '.$type.', ".\xp::typeOf('.$parameters[$j].')." given"'.
                    ');'
                  );
                }
              }
            }

            $annotations= array();
            unset($meta[1][$m][DETAIL_ANNOTATIONS]['generic']);
            continue;
          }
        } else if (4 === $state[0]) {             // Method body
          if ('{' === $tokens[$i][0]) {
            $braces++;
          } else if ('}' === $tokens[$i][0]) {
            $braces--;
            if (0 === $braces) array_shift($state);
          }
        } else if (5 === $state[0]) {             // Implements (class), Extends (interface)
          if (T_STRING === $tokens[$i][0] || T_NS_SEPARATOR === $tokens[$i][0]) {
            $rel= '';
            while ((T_STRING === $tokens[$i][0] || T_NS_SEPARATOR === $tokens[$i][0]) && $i < $s) {
              $rel.= $tokens[$i][1];
              $i++;
            }
            $i--;
            '\\' === $rel{0} || $rel= $namespace.'\\'.$rel;
            if (isset($annotation[$counter])) {
              $iargs= array();
              foreach (explode(',', $annotation[$counter]) as $j => $placeholder) {
                $iargs[]= Type::forName(strtr(ltrim($placeholder), $placeholders));
              }
              $src.= '\\'.$this->newType0(new XPClass(new \ReflectionClass($rel)), $iargs);
            } else {
              $src.= $rel;
            }
            $counter++;
            continue;
          } else if ('{' === $tokens[$i][0]) {
            array_shift($state);
            array_unshift($state, 1);
          }
        }
                  
        $src.= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
      }

      // Create class
      // DEBUG fputs(STDERR, "@* ".substr($src, 0, strpos($src, '{'))." -> $qname\n");
      eval($src);
      method_exists($name, '__static') && call_user_func(array($name, '__static'));
      unset($meta['class'][DETAIL_ANNOTATIONS]['generic']);
      \xp::$meta[$qname]= $meta;
      \xp::$cn[$name]= $qname;

      // Create alias if no PHP namespace is present and a qualified name exists
      if (!$ns && strstr($qname, '.')) {
        class_alias($name, strtr($base->getName(), '.', '\\').'��'.substr($cn, 1));
      }
    }
    
    return $name;
  }
}