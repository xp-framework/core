<?php namespace lang\reflect;

use lang\{XPClass, IllegalStateException, IllegalAccessException, ElementNotFoundException, ClassFormatException};

/**
 * Parses classes for class meta information (apidoc, return and 
 * parameter types, annotations).
 *
 * @test  xp://net.xp_framework.unittest.reflection.ClassDetailsTest
 * @test  xp://net.xp_framework.unittest.annotations.AbstractAnnotationParsingTest
 * @test  xp://net.xp_framework.unittest.annotations.AnnotationParsingTest
 * @test  xp://net.xp_framework.unittest.annotations.BrokenAnnotationTest
 * @test  xp://net.xp_framework.unittest.annotations.MultiValueBCTest
 */
class ClassParser {

  /**
   * Parses and resolves a type
   *
   * @param  var[] $tokens
   * @param  int $i
   * @param  [:string] $context
   * @param  [:string] $imports
   * @return string
   * @throws lang.IllegalStateException
   */
  protected function type($tokens, &$i, $context, $imports) {
    if (T_NAME_FULLY_QUALIFIED === $tokens[$i][0]) {
      return strtr(substr($tokens[$i][1], 1), '\\', '.');
    } else if (T_NS_SEPARATOR === $tokens[$i][0]) {
      $type= '';
      do {
        $type.= '.'.$tokens[$i + 1][1];
        $i+= 2;
      } while (T_NS_SEPARATOR === $tokens[$i][0]);

      $i--; // Position at the last T_STRING token
      return substr($type, 1);
    } else if (T_NAME_QUALIFIED === $tokens[$i][0]) {
      return $context['namespace'].strtr($tokens[$i][1], '\\', '.');
    } else if (T_STRING === $tokens[$i][0]) {
      $type= $tokens[$i][1];
      if ('self' === $type) {
        return $context['self'];
      } else if ('parent' === $type) {
        if (isset($context['parent'])) return $context['parent'];
        throw new IllegalStateException('Class does not have a parent');
      }

      while (T_NS_SEPARATOR === $tokens[$i + 1][0]) {
        $type.= '.'.$tokens[$i + 2][1];
        $i+= 2;
      }
      return $imports[$type] ?? $context['namespace'].$type;
    } else if (T_STATIC === $tokens[$i][0]) {
      return $context['self'];
    } else {
      throw new IllegalStateException(sprintf(
        'Parse error: Unexpected %s',
        is_array($tokens[$i]) ? token_name($tokens[$i][0]) : '"'.$tokens[$i].'"'
      ));
    }
  }

  /**
   * Parses a single value, recursively, if necessary
   *
   * @param  var[] $tokens
   * @param  int $i
   * @param  [:string] $context
   * @param  [:string] $imports
   * @return var
   */
  protected function value($tokens, &$i, $context, $imports) {
    $token= $tokens[$i][0];
    if ('-' === $token) {
      $i++;
      return -1 * $this->value($tokens, $i, $context, $imports);
    } else if ('+' === $token) {
      $i++;
      return +1 * $this->value($tokens, $i, $context, $imports);
    } else if (T_CONSTANT_ENCAPSED_STRING === $token) {
      return eval('return '.$tokens[$i][1].';');
    } else if (T_LNUMBER === $tokens[$i][0]) {
      if (1 === strlen($tokens[$i][1])) {
        return (int)$tokens[$i][1];
      } else if ('x' === $tokens[$i][1][1]) {
        return hexdec($tokens[$i][1]);
      } else if ('0' === $tokens[$i][1][0]) {
        return octdec($tokens[$i][1]);
      } else {
        return (int)$tokens[$i][1];
      }
    } else if (T_DNUMBER === $tokens[$i][0]) {
      return (float)$tokens[$i][1];
    } else if ('[' === $token) {
      $value= [];
      $element= null;
      $key= 0;
      for ($i++, $s= sizeof($tokens); ; $i++) {
        if ($i >= $s) {
          throw new IllegalStateException('Parse error: Unterminated array');
        } else if (']' === $tokens[$i]) {
          $element && $value[$key]= $element[0];
          break;
        } else if ('(' === $tokens[$i]) {
          // Skip
        } else if (',' === $tokens[$i]) {
          if (!$element) throw new IllegalStateException('Parse error: Malformed array - no value before comma');
          $value[$key]= $element[0];
          $element= null;
          $key= sizeof($value);
        } else if (T_DOUBLE_ARROW === $tokens[$i][0]) {
          $key= $element[0];
          $element= null;
        } else if (T_WHITESPACE === $tokens[$i][0]) {
          continue;
        } else {
          if ($element) throw new IllegalStateException('Parse error: Malformed array - missing comma');
          $element= [$this->value($tokens, $i, $context, $imports)];
        }
      }
      return $value;
    } else if ('"' === $token || T_ENCAPSED_AND_WHITESPACE === $token) {
      throw new IllegalStateException('Parse error: Unterminated string');
    } else if (T_FN === $token || T_STRING === $token && 'fn' === $tokens[$i][1]) {
      $s= sizeof($tokens);
      $b= 0;
      $code= '';
      foreach ($imports as $name => $qualified) {
        $code.= 'use '.strtr($qualified, '.', '\\').' as '.$name.';';
      }
      $code.= 'return function';
      for ($i++; $i < $s; $i++) {
        if ('(' === $tokens[$i]) {
          $b++;
          $code.= '(';
        } else if (')' === $tokens[$i]) {
          $b--;
          $code.= ')';
          if (0 === $b) break;
        } else {
          $code.= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
        }
      }

      // Translates => to return statement
      $code.= '{ return ';
      while ($i < $s && T_DOUBLE_ARROW !== $tokens[$i][0]) $i++;

      // Parse expression
      $b= $c= 0;
      for ($i++; $i < $s; $i++) {
        if ('(' === $tokens[$i]) {
          $b++;
          $code.= '(';
        } else if (')' === $tokens[$i]) {
          if (--$b < 0) break;
          $code.= ')';
        } else if ('[' === $tokens[$i]) {
          $c++;
          $code.= '[';
        } else if (']' === $tokens[$i]) {
          if (--$c < 0) break;
          $code.= ']';
        } else if (0 === $b && 0 === $c && ',' === $tokens[$i]) {
          break;
        } else {
          $code.= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
        }
      }
      $i--;
      $code.= '; };';

      try {
        $func= eval($code);
      } catch (\ParseError $e) {
        throw new IllegalStateException('In `'.$code.'`: '.$e->getMessage());
      }
      if (!($func instanceof \Closure)) {
        if ($error= error_get_last()) {
          set_error_handler('__error', 0);
          trigger_error('clear_last_error');
          restore_error_handler();
        } else {
          $error= ['message' => 'Syntax error'];
        }
        throw new IllegalStateException('In `'.$code.'`: '.ucfirst($error['message']));
      }
      return $func;
    } else if (T_NEW === $token) {
      $i+= 2;
      $class= XPClass::forName($this->type($tokens, $i, $context, $imports));

      $i+= 2;
      for ($args= [], $arg= null, $s= sizeof($tokens); ; $i++) {
        if (')' === $tokens[$i]) {
          $arg && $args[]= $arg[0];
          break;
        } else if (',' === $tokens[$i]) {
          $args[]= $arg[0];
          $arg= null;
        } else if (T_WHITESPACE !== $tokens[$i][0]) {
          $arg= [$this->value($tokens, $i, $context, $imports)];
        }
      }
      return $class->newInstance(...$args);
    } else if (T_FUNCTION === $token) {
      $b= 0;
      $code= '';
      foreach ($imports as $name => $qualified) {
        $code.= 'use '.strtr($qualified, '.', '\\').' as '.$name.';';
      }
      $code.= 'return function';
      for ($i++, $s= sizeof($tokens); $i < $s; $i++) {
        if ('{' === $tokens[$i] || T_CURLY_OPEN === $tokens[$i][0]) {
          $b++;
          $code.= '{';
        } else if ('}' === $tokens[$i]) {
          $b--;
          $code.= '}';
          if (0 === $b) break;
        } else {
          $code.= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
        }
      }
      try {
        $func= eval($code.';');
      } catch (\ParseError $e) {
        throw new IllegalStateException('In `'.$code.'`: '.$e->getMessage());
      }
      if (!($func instanceof \Closure)) {
        if ($error= error_get_last()) {
          set_error_handler('__error', 0);
          trigger_error('clear_last_error');
          restore_error_handler();
        } else {
          $error= ['message' => 'Syntax error'];
        }
        throw new IllegalStateException('In `'.$code.'`: '.ucfirst($error['message']));
      }
      return $func;
    } else if (T_STRING === $token && T_NS_SEPARATOR !== $tokens[$i + 1][0] && T_DOUBLE_COLON !== $tokens[$i + 1][0]) {
      if (defined($tokens[$i][1])) return constant($tokens[$i][1]);
      throw new ElementNotFoundException('Undefined constant "'.$tokens[$i][1].'"');
    } else {
      $class= XPClass::forName($this->type($tokens, $i, $context, $imports));
      $i+= 2;
      if (T_VARIABLE === $tokens[$i][0]) {
        $name= substr($tokens[$i][1], 1);
        $reflect= $class->reflect();
        if (!$reflect->hasProperty($name)) {
          throw new ElementNotFoundException('No such field "'.$name.'" in class '.$class->getName());
        }
        $field= $class->reflect()->getProperty($name);
        $m= $field->getModifiers();
        if ($m & MODIFIER_PUBLIC) {
          return $field->getValue(null);
        } else if (($m & MODIFIER_PROTECTED) && $class->isAssignableFrom($context['self'])) {
          $field->setAccessible(true);
          return $field->getValue(null);
        } else if (($m & MODIFIER_PRIVATE) && $class->getName() === $context['self']) {
          $field->setAccessible(true);
          return $field->getValue(null);
        } else {
          throw new IllegalAccessException(sprintf(
            'Cannot access %s field %s::$%s',
            implode(' ', Modifiers::namesOf($m)),
            $class->getName(),
            $field->getName()
          ));
        }
      } else if (T_CLASS === $tokens[$i][0]) {
        return $class->literal();
      } else {
        $name= $tokens[$i][1];
        $reflect= $class->reflect();
        if (!$reflect->hasConstant($name)) {
          throw new ElementNotFoundException('No such constant "'.$name.'" in class '.$class->getName());
        }
        return $reflect->getConstant($name);
      }
    }
  }

  /**
   * Parses annotation string
   *
   * @param   string bytes
   * @param   [:string] context
   * @return  [:string] imports
   * @param   int line 
   * @return  [:var]
   * @throws  lang.ClassFormatException
   */
  public function parseAnnotations($bytes, $context, $imports= [], $line= -1) {
    static $states= [
      'annotation', 'annotation name', 'annotation value',
      'annotation map key', 'annotation map value',
      'multi-value'
    ];

    // BC when class name is passed for context
    if (is_string($context)) {
      $parent= get_parent_class(strtr($context, '.', '\\'));
      $namespace= false === ($p= strrpos($context, '.')) ? '' : substr($context, 0, $p + 1);
      $context= ['self' => $context, 'parent' => $parent ? strtr($parent, '\\', '.') : null, 'namespace' => $namespace];
    }

    $tokens= token_get_all('<?php '.trim($bytes, "[# \t\n\r"));
    $annotations= [0 => [], 1 => []];
    $place= $context['self'].(-1 === $line ? '' : ', line '.$line);

    // Parse tokens
    try {
      for ($state= 0, $i= 1, $s= sizeof($tokens); $i < $s; $i++) {
        if (T_WHITESPACE === $tokens[$i][0]) {
          continue;
        } else if (0 === $state) {              // Initial state, expecting @attr or @$param: attr
          if ('@' === $tokens[$i]) {
            $annotation= $tokens[$i + 1][1];
            $param= null;
            $value= null;
            $i++;
            $state= 1;
            trigger_error('XP annotation syntax is deprecated in '.$place, E_USER_DEPRECATED);
          } else if (']' === $tokens[$i]) {     // Handle situations with trailing comma
            $annotations[0][$annotation]= $value;
            return $annotations;
          } else {
            $type= $this->type($tokens, $i, $context, $imports);
            $annotation= lcfirst(false === ($p= strrpos($type, '.')) ? $type : substr($type, $p + 1));
            $annotations[1][$annotation]= $type;
            $param= null;
            $value= null;
            $state= 1;
          }
        } else if (1 === $state) {              // Inside attribute, check for values
          if ('(' === $tokens[$i]) {
            $state= 2;
          } else if (',' === $tokens[$i]) {
            if ($param) {
              $annotations[1][$param][$annotation]= $value;
            } else {
              $annotations[0][$annotation]= $value;
            }
            $state= 0;
          } else if (']' === $tokens[$i]) {
            if ($param) {
              $annotations[1][$param][$annotation]= $value;
            } else {
              $annotations[0][$annotation]= $value;
            }
            return $annotations;
          } else if (':' === $tokens[$i]) {
            $param= $annotation;
            $annotation= null;
          } else if (T_STRING === $tokens[$i][0]) {
            $annotation= $tokens[$i][1];
          } else {
            throw new IllegalStateException('Parse error: Expecting either "(", "," or "]", have '.(is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i]));
          }
        } else if (2 === $state) {              // Inside braces of @attr(...)
          if (')' === $tokens[$i]) {
            $state= 1;
          } else if ($i + 2 < $s && (':' === $tokens[$i + 1] || ':' === $tokens[$i + 2])) {
            $key= $tokens[$i][1];

            if ('eval' === $key) {              // Attribute(eval: '...') vs. Attribute(name: ...)
              while ($i++ < $s && ':' === $tokens[$i] || T_WHITESPACE === $tokens[$i][0]) { }

              $code= $this->value($tokens, $i, $context, $imports);
              if (is_string($code)) {
                $eval= token_get_all('<?php '.$code);
              } else if (is_string(key($code))) {
                $pairs= '';
                foreach ($code as $named => $expr) {
                  $pairs.= "'".strtr($named, ["'" => "\\'"])."'=>{$expr},";
                }
                $eval= token_get_all('<?php ['.$pairs.']');
              } else if (1 !== sizeof($code)) {
                throw new IllegalStateException('Unexpected "," in eval');
              } else {
                $eval= token_get_all('<?php '.current($code));
              }

              $j= 1;
              $value= $this->value($eval, $j, $context, $imports);
            } else {
              $value= [];
              $state= 3;
            }
          } else {
            $value= $this->value($tokens, $i, $context, $imports);
          }
        } else if (3 === $state) {              // Parsing key inside named arguments
          if (')' === $tokens[$i]) {
            $state= 1;
          } else if (',' === $tokens[$i]) {
            $key= null;
          } else if ('=' === $tokens[$i] || ':' === $tokens[$i]) {
            $state= 4;
          } else if (is_array($tokens[$i])) {
            $key= $tokens[$i][1];
          }
        } else if (4 === $state) {              // Parsing value inside named arguments
          $value[$key]= $this->value($tokens, $i, $context, $imports);
          $state= 3;
        }
      }
    } catch (\lang\XPException $e) {
      throw new ClassFormatException($e->getMessage().' in '.$place, $e);
    }
    throw new ClassFormatException('Parse error: Unterminated '.$states[$state].' in '.$place);
  }

  /**
   * Returns position of matching closing brace, or the string's length
   * if no closing / opening brace is found.
   *
   * @param  string $text
   * @param  string $open
   * @param  string $close
   * @param  int
   */
  protected static function matching($text, $open, $close) {
    for ($braces= $open.$close, $i= 0, $b= 0, $s= strlen($text); $i < $s; $i+= strcspn($text, $braces, $i)) {
      if ($text[$i] === $open) {
        $b++;
      } else if ($text[$i] === $close) {
        if (0 === --$b) return $i + 1;
      }
      $i++;
    }
    return $i;
  }

  /**
   * Extracts type from a text
   *
   * @param  string $text
   * @param  [:string] $imports
   * @return string
   */
  public static function typeIn($text, $imports) {
    if (0 === strncmp($text, 'function(', 9)) {
      $p= self::matching($text, '(', ')');
      $p+= strspn($text, ': ', $p);
      return substr($text, 0, $p).self::typeIn(substr($text, $p), $imports);
    } else if (0 === strncmp($text, '(function(', 10)) {
      $p= self::matching($text, '(', ')');
      return substr($text, 0, $p).self::typeIn(substr($text, $p), $imports);
    } else if ('[' === $text[0]) {
      $p= self::matching($text, '[', ']');
      return substr($text, 0, $p);
    } else if (strstr($text, '<')) {
      $p= self::matching($text, '<', '>');
      $type= substr($text, 0, $p);
    } else {
      $type= substr($text, 0, strcspn($text, ' '));
    }

    if ('\\' === ($type[0] ?? null)) {
      return strtr(substr($type, 1), '\\', '.');
    } else if (isset($imports[$type])) {
      return $imports[$type];
    } else {
      return $type;
    }
  }

  /**
   * Parse details from a given input string
   *
   * @param   string bytes
   * @return  [:var] details
   */
  public function parseDetails($bytes) {
    $details= [[], []];
    $annotations= [0 => [], 1 => []];
    $imports= [];
    $comment= '';
    $parsed= '';
    $context= ['namespace' => '', 'self' => null, 'parent' => null];
    $tokens= token_get_all($bytes);
    for ($i= 0, $s= sizeof($tokens); $i < $s; $i++) {
      switch ($tokens[$i][0]) {
        case T_NAMESPACE:
          $namespace= '';
          for ($i+= 2; $i < $s, !(';' === $tokens[$i] || T_WHITESPACE === $tokens[$i][0]); $i++) {
            $namespace.= $tokens[$i][1];
          }
          $context['namespace']= strtr($namespace, '\\', '.').'.';
          break;

        case T_USE:
          if (isset($details['class'])) break;  // Inside class, e.g. function() use(...) {}

          do {
            $type= '';
            for ($i+= 2; $i < $s, !(';' === $tokens[$i] || '{' === $tokens[$i] || ',' === $tokens[$i] || T_WHITESPACE === $tokens[$i][0]); $i++) {
              $type.= $tokens[$i][1];
            }

            // use lang\{Type, Primitive as P}
            if ('{' === $tokens[$i]) {
              $alias= null;
              $group= '';
              for ($i+= 1; $i < $s; $i++) {
                if (',' === $tokens[$i]) {
                  $imports[$alias ? $alias : $group]= strtr($type.$group, '\\', '.');
                  $alias= null;
                  $group= '';
                } else if ('}' === $tokens[$i]) {
                  $i++;
                  $imports[$alias ? $alias : $group]= strtr($type.$group, '\\', '.');
                  break;
                } else if (T_AS === $tokens[$i][0]) {
                  $i+= 2;
                  $alias= $tokens[$i][1];
                } else if (T_WHITESPACE !== $tokens[$i][0]) {
                  $group.= $tokens[$i][1];
                }
              }
            } else if (T_AS === $tokens[$i + 1][0]) {
              $i+= 3;
              $imports[$tokens[$i][1]]= strtr($type, '\\', '.');
            } else {
              $p= strrpos($type, '\\');
              $imports[false === $p ? $type : substr($type, $p + 1)]= strtr($type, '\\', '.');
            }
          } while (',' === $tokens[$i]);
          break;

        case T_DOC_COMMENT:
          $comment= $tokens[$i][1];
          break;

        case T_ATTRIBUTE:                       // PHP 8 attributes
          $b= 1;
          $parsed= '';
          while ($i++ < $s) {
            $parsed.= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
            if ('[' === $tokens[$i]) {
              $b++;
            } else if (']' === $tokens[$i]) {
              if (0 === --$b) break;
            }
          }
          break;

        case T_COMMENT:
          if ('#' === $tokens[$i][1][0]) {      // Annotations, #[@test]
            if ('[' === $tokens[$i][1][1]) {
              $parsed= substr($tokens[$i][1], 2);
            } else {
              $parsed.= substr($tokens[$i][1], 1);
            }
          }
          break;

        case T_NEW:                             // Anonymous class
          $details['class']= [
            DETAIL_COMMENT      => null,
            DETAIL_ANNOTATIONS  => [],
            DETAIL_ARGUMENTS    => null
          ];
          $annotations= [0 => [], 1 => []];
          $comment= '';

          $b= 0;
          while (++$i < $s) {
            if ('(' === $tokens[$i][0]) {
              $b++;
            } else if (')' === $tokens[$i][0]) {
              if (0 === --$b) break;
            } else if (0 === $b && ';' === $tokens[$i][0]) {
              break;    // Abstract or interface method
            }
          }
          break;

        case T_CLASS:
          if (isset($details['class'])) break;  // Inside class, e.g. $lookup= ['self' => self::class]

        case T_INTERFACE: case T_TRAIT: case T_ENUM:
          $context['self']= $context['namespace'].$tokens[$i + 2][1];
          if ($parsed) {
            $annotations= $this->parseAnnotations($parsed, $context, $imports, $tokens[$i][2] ?? -1);
            $parsed= '';
          }
          $details['class']= [
            DETAIL_COMMENT      => trim(preg_replace('/\n\s+\* ?/', "\n", "\n".substr(
              $comment, 
              4,                              // "/**\n"
              strpos($comment, '* @')- 2      // position of first details token
            ))),
            DETAIL_ANNOTATIONS  => $annotations[0],
            DETAIL_TARGET_ANNO  => $annotations[1]
          ];
          $annotations= [0 => [], 1 => []];
          $comment= '';
          break;

        case T_EXTENDS:
          $i+= 2;
          $context['parent']= $this->type($tokens, $i, $context, $imports);
          break;

        case T_VARIABLE:                      // Have a member variable
          if ($parsed) {
            $annotations= $this->parseAnnotations($parsed, $context, $imports, $tokens[$i][2] ?? -1);
            $parsed= '';
          }
          $f= substr($tokens[$i][1], 1);
          $details[0][$f]= [DETAIL_ANNOTATIONS => $annotations[0], DETAIL_TARGET_ANNO  => $annotations[1]];
          $annotations= [0 => [], 1 => []];
          $matches= null;
          if ('' === $comment) break;
          preg_match_all('/@([a-z]+)\s*([^\r\n]+)?/', $comment, $matches, PREG_SET_ORDER);
          foreach ((array)$matches as $match) {
            if ('var' === $match[1] || 'type' === $match[1]) {
              $details[0][$f][DETAIL_RETURNS]= self::typeIn($match[2], $imports);
            }
          }
          $comment= '';
          break;

        case T_FUNCTION:
          if ($parsed) {
            $annotations= $this->parseAnnotations($parsed, $context, $imports, $tokens[$i][2] ?? -1);
            $parsed= '';
          }
          $i+= 2;
          $m= $tokens[$i][1];
          $details[1][$m]= [
            DETAIL_ARGUMENTS    => [],
            DETAIL_RETURNS      => null,
            DETAIL_THROWS       => [],
            DETAIL_COMMENT      => trim(preg_replace('/\n\s+\* ?/', "\n", "\n".substr(
              $comment, 
              4,                              // "/**\n"
              strpos($comment, '* @')- 2      // position of first details token
            ))),
            DETAIL_ANNOTATIONS  => $annotations[0],
            DETAIL_TARGET_ANNO  => $annotations[1]
          ];
          $annotations= [0 => [], 1 => []];
          $matches= null;
          preg_match_all('/@([a-z]+)\s*([^\r\n]+)?/', $comment, $matches, PREG_SET_ORDER);
          $comment= '';
          $arg= 0;
          foreach ($matches as $match) {
            switch ($match[1]) {
              case 'param':
                $details[1][$m][DETAIL_ARGUMENTS][$arg++]= self::typeIn($match[2], $imports);
                break;

              case 'return':
                $details[1][$m][DETAIL_RETURNS]= self::typeIn($match[2], $imports);
                break;

              case 'throws': 
                $details[1][$m][DETAIL_THROWS][]= self::typeIn($match[2], $imports);
                break;
            }
          }

          $b= 0;
          $parsed= null;
          while (++$i < $s) {
            if ('(' === $tokens[$i][0]) {
              $b++;
            } else if (')' === $tokens[$i][0]) {
              if (0 === --$b) break;
            } else if (T_COMMENT === $tokens[$i][0]) {
              $parsed= $tokens[$i][1];
            } else if (T_ATTRIBUTE === $tokens[$i][0]) {
              $e= 1;
              $parsed= '';
              while ($i++ < $s) {
                $parsed.= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                if ('[' === $tokens[$i]) {
                  $e++;
                } else if (']' === $tokens[$i]) {
                  if (0 === --$e) break;
                }
              }
            } else if (T_VARIABLE === $tokens[$i][0] && null !== $parsed) {
              $annotations= $this->parseAnnotations(
                $parsed,
                $context,
                $imports,
                $tokens[$i][2] ?? -1
              );
              $details[1][$m][DETAIL_TARGET_ANNO][$tokens[$i][1]]= $annotations[0];
              $details[1][$m][DETAIL_TARGET_ANNO]+= $annotations[1];
              $parsed= null;
            }
          }

          $b= 0;
          while (++$i < $s) {
            if ('{' === $tokens[$i] || T_CURLY_OPEN === $tokens[$i][0]) {
              $b++;
            } else if ('}' === $tokens[$i]) {
              if (0 === --$b) break;
            } else if (0 === $b && ';' === $tokens[$i]) {
              break;    // Abstract or interface method
            }
          }
          break;

        default:
          // Empty
      }
    }
    return $details;
  }
}