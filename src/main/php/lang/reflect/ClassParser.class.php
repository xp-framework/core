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
    } else if (class_exists($type, false) || interface_exists($type, false)) {
      return new XPClass($type);
    } else if (false !== ($p= strrpos($context, '.'))) {
      return XPClass::forName(substr($context, 0, $p + 1).$type);
    }
  }

  /**
   * Resolves a class member, which is either a field, a class constant
   * or the `ClassName::class` syntax, which returns the class' literal.
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
    } else if (T_CLASS === $token[0]) {
      return $class->literal();
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
      $value= [];
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
          $element= [$this->valueOf($tokens, $i, $context, $imports)];
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
      for ($args= [], $arg= null, $s= sizeof($tokens); ; $i++) {
        if (')' === $tokens[$i]) {
          $arg && $args[]= $arg[0];
          break;
        } else if (',' === $tokens[$i]) {
          $args[]= $arg[0];
          $arg= null;
        } else if (T_WHITESPACE !== $tokens[$i][0]) {
          $arg= [$this->valueOf($tokens, $i, $context, $imports)];
        }
      }
      return $class->hasConstructor() ? $class->getConstructor()->newInstance($args) : $class->newInstance();
    } else if (T_FUNCTION === $tokens[$i][0]) {
      $b= 0;
      $code= 'function';
      for ($i++, $s= sizeof($tokens); $i < $s; $i++) {
        if ('{' === $tokens[$i]) {
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
      $func= eval('return '.$code.';');
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
  public function parseAnnotations($bytes, $context, $imports= [], $line= -1) {
    static $states= [
      'annotation', 'annotation name', 'annotation value',
      'annotation map key', 'annotation map value',
      'multi-value'
    ];

    $annotations= [0 => [], 1 => []];
    if (null === $bytes) return $annotations;

    $tokens= token_get_all('<?php '.trim($bytes, "[# \t\n\r"));
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
          } else if ($i + 2 < $s && ('=' === $tokens[$i + 1] || '=' === $tokens[$i + 2])) {
            $key= $tokens[$i][1];
            $value= [];
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
    $codeunit= (new \lang\codedom\PhpSyntax())->parse(ltrim($bytes));

    $imports= [];
    foreach ($codeunit->imports() as $type) {
      $imports[substr($type, strrpos($type, '.')+ 1)]= $type;
    }

    $decl= $codeunit->declaration();
    $annotations= $this->parseAnnotations($decl->annotations(), $context, $imports, -1);
    $details= [
      0 => [],    // Fields
      1 => [],    // Methods
      'class' => [DETAIL_ANNOTATIONS  => $annotations[0]]
    ];

    foreach ($decl->body()->members() as $member) {
      $annotations= $this->parseAnnotations($member->annotations(), $context, $imports, -1);
      if ($member->isField()) {
        $details[0][$member->name()]= [DETAIL_ANNOTATIONS => $annotations[0]];
      } else if ($member->isMethod()) {
        $details[1][$member->name()]= [
          DETAIL_ARGUMENTS    => $member->arguments(),
          DETAIL_RETURNS      => $member->returns(),
          DETAIL_THROWS       => $member->throws(),
          DETAIL_ANNOTATIONS  => $annotations[0],
          DETAIL_TARGET_ANNO  => $annotations[1]
        ];
      }
    }

    return $details;
  }
}