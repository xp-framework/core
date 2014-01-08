<?php namespace lang\reflect;

use lang\XPClass;
use lang\IllegalStateException;
use lang\IllegalAccessException;

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
class ClassParser extends \lang\Object {

  /**
   * Resolves a type in a given context. Recognizes classes imported via
   * the `use` statement.
   *
   * @param  string $type
   * @param  string $context
   * @param  [:string] $imports
   * @return lang.XPClass
   */
  protected function resolve($type, $context, $imports) {
    if ('self' === $type) {
      return XPClass::forName($context);
    } else if ('parent' === $type) {
      return XPClass::forName($context)->getParentclass();
    } else if (false !== strpos($type, '.')) {
      return XPClass::forName($type);
    } else if (isset($imports[$type])) {
      return XPClass::forName($imports[$type]);
    } else if (isset(\xp::$cn[$type])) {
      return XPClass::forName(\xp::$cn[$type]);
    } else if (false !== ($p= strrpos($context, '.'))) {
      return XPClass::forName(substr($context, 0, $p + 1).$type);
    } else {
      return XPClass::forName($type);
    }
  }

  /**
   * Resolves a class member, which is either a field or a constant.
   *
   * @param  lang.XPClass $class
   * @param  var[] $token A token as returned by `token_get_all()`
   * @param  string $context
   * @return var
   */
  protected function memberOf($class, $token, $context) {
    if (T_VARIABLE === $token[0]) {
      $field= $class->getField(substr($token[1], 1));
      $m= $field->getModifiers();
      if ($m & MODIFIER_PUBLIC) {
        return $field->get(null);
      } else if (($m & MODIFIER_PROTECTED) && $class->isAssignableFrom($context)) {
        return $field->setAccessible(true)->get(null);
      } else if (($m & MODIFIER_PRIVATE) && $class->getName() === $context) {
        return $field->setAccessible(true)->get(null);
      } else {
        throw new IllegalAccessException(sprintf(
          'Cannot access %s field %s::$%s',
          implode(' ', Modifiers::namesOf($m)),
          $class->getName(),
          $field->getName()
        ));
      }
    } else {
      return $class->getConstant($token[1]);
    }
  }

  /**
   * Parses a single value, recursively, if necessary
   *
   * @param  var[] $tokens
   * @param  int $i
   * @param  string $context
   * @param  [:string] $imports
   * @return var
   */
  protected function valueOf($tokens, &$i, $context, $imports) {
    if ('-' ===  $tokens[$i][0]) {
      $i++;
      return -1 * $this->valueOf($tokens, $i, $context, $imports);
    } else if ('+' === $tokens[$i][0]) {
      $i++;
      return +1 * $this->valueOf($tokens, $i, $context, $imports);
    } else if (T_CONSTANT_ENCAPSED_STRING === $tokens[$i][0]) {
      return eval('return '.$tokens[$i][1].';');
    } else if (T_LNUMBER === $tokens[$i][0]) {
      return (int)$tokens[$i][1];
    } else if (T_DNUMBER === $tokens[$i][0]) {
      return (double)$tokens[$i][1];
    } else if ('[' === $tokens[$i] || T_ARRAY === $tokens[$i][0]) {
      $value= array();
      $element= null;
      $key= 0;
      $end= '[' === $tokens[$i] ? ']' : ')';
      for ($i++, $s= sizeof($tokens); ; $i++) {
        if ($i >= $s) {
          throw new IllegalStateException('Parse error: Unterminated array');
        } else if ($end === $tokens[$i]) {
          $element && $value[$key]= $element[0];
          break;
        } else if ('(' === $tokens[$i]) {
          // Skip
        } else if (',' === $tokens[$i]) {
          $element || raise('lang.IllegalStateException', 'Parse error: Malformed array - no value before comma');
          $value[$key]= $element[0];
          $element= null;
          $key= sizeof($value);
        } else if (T_DOUBLE_ARROW === $tokens[$i][0]) {
          $key= $element[0];
          $element= null;
        } else if (T_WHITESPACE === $tokens[$i][0]) {
          continue;
        } else {
          $element && raise('lang.IllegalStateException', 'Parse error: Malformed array - missing comma');
          $element= array($this->valueOf($tokens, $i, $context, $imports));
        }
      }
      return $value;
    } else if ('"' === $tokens[$i] || T_ENCAPSED_AND_WHITESPACE === $tokens[$i][0]) {
      throw new IllegalStateException('Parse error: Unterminated string');
    } else if (T_NS_SEPARATOR === $tokens[$i][0]) {
      $type= '';
      while (T_NS_SEPARATOR === $tokens[$i++][0]) {
        $type.= '.'.$tokens[$i++][1];
      }
      return $this->memberOf(XPClass::forName(substr($type, 1)), $tokens[$i], $context);
    } else if (T_STRING === $tokens[$i][0]) {     // constant vs. class::constant
      if (T_DOUBLE_COLON === $tokens[$i + 1][0]) {
        $i+= 2;
        return $this->memberOf($this->resolve($tokens[$i - 2][1], $context, $imports), $tokens[$i], $context);
      } else if (defined($tokens[$i][1])) {
        return constant($tokens[$i][1]);
      } else {
        raise('lang.ElementNotFoundException', 'Undefined constant "'.$tokens[$i][1].'"');
      }
    } else if (T_NEW === $tokens[$i][0]) {
      $type= '';
      while ('(' !== $tokens[$i++]) {
        if (T_STRING === $tokens[$i][0]) $type.= '.'.$tokens[$i][1];
      }
      $class= $this->resolve(substr($type, 1), $context, $imports);
      for ($args= array(), $arg= null, $s= sizeof($tokens); ; $i++) {
        if (')' === $tokens[$i]) {
          $arg && $args[]= $arg[0];
          break;
        } else if (',' === $tokens[$i]) {
          $args[]= $arg[0];
          $arg= null;
        } else if (T_WHITESPACE !== $tokens[$i][0]) {
          $arg= array($this->valueOf($tokens, $i, $context, $imports));
        }
      }
      return $class->hasConstructor() ? $class->getConstructor()->newInstance($args) : $class->newInstance();
    } else {
      throw new IllegalStateException(sprintf(
        'Parse error: Unexpected %s',
        is_array($tokens[$i]) ? token_name($tokens[$i][0]) : '"'.$tokens[$i].'"'
      ));
    }
  }

  /**
   * Parses annotation string
   *
   * @param   string bytes
   * @param   string context the class name
   * @return  [:string] imports
   * @param   int line 
   * @return  [:var]
   * @throws  lang.ClassFormatException
   */
  public function parseAnnotations($bytes, $context, $imports= array(), $line= -1) {
    static $states= array(
      'annotation', 'annotation name', 'annotation value',
      'annotation map key', 'annotation map value',
      'multi-value'
    );

    $tokens= token_get_all('<?php '.trim($bytes, "[# \t\n\r"));
    $annotations= array(0 => array(), 1 => array());
    $place= $context.(-1 === $line ? '' : ', line '.$line);

    // Parse tokens
    try {
      for ($state= 0, $i= 1, $s= sizeof($tokens); $i < $s; $i++) {
        if (T_WHITESPACE === $tokens[$i][0]) {
          continue;
        } else if (0 === $state) {             // Initial state, expecting @attr or @$param: attr
          if ('@' === $tokens[$i]) {
            $annotation= $tokens[$i + 1][1];
            $param= null;
            $value= null;
            $i++;
            $state= 1;
          } else {
            throw new IllegalStateException('Parse error: Expecting "@"');
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
            throw new IllegalStateException('Parse error: Expecting either "(", "," or "]"');
          }
        } else if (2 === $state) {              // Inside braces of @attr(...)
          if (')' === $tokens[$i]) {
            $state= 1;
          } else if (',' === $tokens[$i]) {
            trigger_error('Deprecated usage of multi-value annotations in '.$place, E_USER_DEPRECATED);
            $value= (array)$value;
            $state= 5;
          } else if ($i + 2 < $s && ('=' === $tokens[$i + 1] || '=' === $tokens[$i + 2])) {
            $key= $tokens[$i][1];
            $value= array();
            $state= 3;
          } else {
            $value= $this->valueOf($tokens, $i, $context, $imports);
          }
        } else if (3 === $state) {              // Parsing key inside @attr(a= b, c= d)
          if (')' === $tokens[$i]) {
            $state= 1;
          } else if (',' === $tokens[$i]) {
            $key= null;
          } else if ('=' === $tokens[$i]) {
            $state= 4;
          } else if (is_array($tokens[$i])) {
            $key= $tokens[$i][1];
          }
        } else if (4 === $state) {              // Parsing value inside @attr(a= b, c= d)
          $value[$key]= $this->valueOf($tokens, $i, $context, $imports);
          $state= 3;
        } else if (5 === $state) {
          if (')' === $tokens[$i]) {            // BC: Deprecated multi-value annotations
            $value[]= $element;
            $state= 1;
          } else if (',' === $tokens[$i]) {
            $value[]= $element;
          } else {
            $element= $this->valueOf($tokens, $i, $context, $imports);
          }
        }
      }
    } catch (\lang\XPException $e) {
      raise('lang.ClassFormatException', $e->getMessage().' in '.$place, $e);
    }
    raise('lang.ClassFormatException', 'Parse error: Unterminated '.$states[$state].' in '.$place);
  }

  /**
   * Parse details from a given input string
   *
   * @param   string bytes
   * @param   string context default ''
   * @return  [:var] details
   */
  public function parseDetails($bytes, $context= '') {
    $details= array(array(), array());
    $annotations= array(0 => array(), 1 => array());
    $imports= array();
    $comment= null;
    $members= true;
    $parsed= '';
    $tokens= token_get_all($bytes);
    for ($i= 0, $s= sizeof($tokens); $i < $s; $i++) {
      switch ($tokens[$i][0]) {
        case T_USE:
          if (isset($details['class'])) break;  // Inside class, e.g. function() use(...) {}
          $type= '';
          while (';' !== $tokens[++$i] && $i < $s) {
            T_WHITESPACE === $tokens[$i][0] || $type.= $tokens[$i][1];
          }
          $imports[substr($type, strrpos($type, '\\')+ 1)]= strtr($type, '\\', '.');
          break;

        case T_DOC_COMMENT:
          $comment= $tokens[$i][1];
          break;

        case T_COMMENT:
          if ('#' === $tokens[$i][1]{0}) {      // Annotations, #[@test]
            if ('[' === $tokens[$i][1]{1}) {
              $parsed= substr($tokens[$i][1], 2);
            } else {
              $parsed.= substr($tokens[$i][1], 1);
            }
          }
          break;

        case T_CLASS:
        case T_INTERFACE:
          if ($parsed) {
            $annotations= $this->parseAnnotations($parsed, $context, $imports, isset($tokens[$i][2]) ? $tokens[$i][2] : -1);
            $parsed= '';
          }
          $details['class']= array(
            DETAIL_COMMENT      => trim(preg_replace('/\n\s+\* ?/', "\n", "\n".substr(
              $comment, 
              4,                              // "/**\n"
              strpos($comment, '* @')- 2      // position of first details token
            ))),
            DETAIL_ANNOTATIONS  => $annotations[0]
          );
          $annotations= array(0 => array(), 1 => array());
          $comment= null;
          break;

        case T_VARIABLE:                      // Have a member variable
          if (!$members) break;
          if ($parsed) {
            $annotations= $this->parseAnnotations($parsed, $context, $imports, isset($tokens[$i][2]) ? $tokens[$i][2] : -1);
            $parsed= '';
          }
          $name= substr($tokens[$i][1], 1);
          $details[0][$name]= array(
            DETAIL_ANNOTATIONS => $annotations[0]
          );
          $annotations= array(0 => array(), 1 => array());
          break;

        case T_FUNCTION:
          if (T_STRING !== $tokens[$i+ 2][0]) break;    // A closure, `function($params) { return TRUE; }`
          if ($parsed) {
            $annotations= $this->parseAnnotations($parsed, $context, $imports, isset($tokens[$i][2]) ? $tokens[$i][2] : -1);
            $parsed= '';
          }
          $members= false;
          $i+= 2;
          $m= $tokens[$i][1];
          $details[1][$m]= array(
            DETAIL_ARGUMENTS    => array(),
            DETAIL_RETURNS      => null,
            DETAIL_THROWS       => array(),
            DETAIL_COMMENT      => trim(preg_replace('/\n\s+\* ?/', "\n", "\n".substr(
              $comment, 
              4,                              // "/**\n"
              strpos($comment, '* @')- 2      // position of first details token
            ))),
            DETAIL_ANNOTATIONS  => $annotations[0],
            DETAIL_TARGET_ANNO  => $annotations[1]
          );
          $annotations= array(0 => array(), 1 => array());
          $matches= null;
          preg_match_all(
            '/@([a-z]+)\s*([^<\r\n]+<[^>]+>|[^\r\n ]+) ?([^\r\n ]+)?/',
            $comment, 
            $matches, 
            PREG_SET_ORDER
          );
          $comment= null;
          $arg= 0;
          foreach ($matches as $match) {
            switch ($match[1]) {
              case 'param':
                $details[1][$m][DETAIL_ARGUMENTS][$arg++]= $match[2];
                break;

              case 'return':
                $details[1][$m][DETAIL_RETURNS]= $match[2];
                break;

              case 'throws': 
                $details[1][$m][DETAIL_THROWS][]= $match[2];
                break;
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