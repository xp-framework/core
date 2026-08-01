<?php namespace lang;

use ReflectionClass;

/** @test lang.unittest.ClassMetaTest */
class ClassMeta {

  /**
   * Returns the comment text
   *
   * @param  string $comment
   * @param  ?int $p
   * @return string
   */
  private function comment($comment, $p= null) {
    return trim(preg_replace('/\n\s+\* ?/', "\n", "\n".substr(
      $comment, 
      3,                                  // "/**[ \n]"
      ($p ?? strpos($comment, '* @')) - 2 // position of first details token
    )));
  }

  /**
   * Returns position of matching closing brace, or the string's length
   * if no closing / opening brace is found.
   *
   * @param  string $input
   * @param  string $open
   * @param  string $close
   * @param  int
   */
  private function matching($input, $open, $close) {
    for ($braces= $open.$close, $i= 0, $b= 0, $s= strlen($input); $i < $s; $i+= strcspn($input, $braces, $i)) {
      if ($input[$i] === $open) {
        $b++;
      } else if ($input[$i] === $close) {
        if (0 === --$b) return $i + 1;
      }
      $i++;
    }
    return $i;
  }

  /**
   * Extracts type from a comment
   *
   * @param  string $comment
   * @param  ReflectionClass $reflect
   * @return string
   */
  private function type($comment, $reflect= []) {
    if (0 === strncmp($comment, 'function(', 9)) {
      $p= $this->matching($comment, '(', ')');
      $p+= strspn($comment, ': ', $p);
      return substr($comment, 0, $p).$this->type(substr($comment, $p), $reflect);
    } else if (0 === strncmp($comment, '(function(', 10)) {
      $p= $this->matching($comment, '(', ')');
      return substr($comment, 0, $p).$this->type(substr($comment, $p), $reflect);
    } else if ('[' === $comment[0]) {
      $p= $this->matching($comment, '[', ']');
      return substr($comment, 0, $p);
    } else if (strstr($comment, '<')) {
      $p= $this->matching($comment, '<', '>');
      $type= substr($comment, 0, $p);
    } else {
      $type= substr($comment, 0, strcspn($comment, ' '));
    }

    if ('\\' === ($type[0] ?? null)) {
      return strtr(substr($type, 1), '\\', '.');
    } else {
      return $type;
    }
  }

  /**
   * Returns imports used in the class file the given class was declared in
   *
   * @param  ReflectionClass $reflect
   * @return [:string]
   */
  public function imports($reflect) {
    static $break= [T_CLASS => true, T_INTERFACE => true, T_TRAIT => true, 372 /* T_ENUM */ => true];
    static $types= [T_WHITESPACE => true, 44 => true, 59 => true, 123 => true];

    // Exclude classes declared inside eval(), their declaration is not accessible
    $file= $reflect->getFileName();
    if (false !== strpos($file, ': eval')) return [];

    $tokens= PhpToken::tokenize(file_get_contents($file));
    $imports= [];
    for ($i= 0, $s= sizeof($tokens); $i < $s; $i++) {
      if (isset($break[$tokens[$i]->id])) break;
      if (T_USE !== $tokens[$i]->id) continue;

      do {
        $type= '';
        for ($i+= 2; $i < $s, !isset($types[$tokens[$i]->id]); $i++) {
          $type.= $tokens[$i]->text;
        }

        // Skip over whitespace
        if (T_WHITESPACE === $tokens[$i]->id) $i++;

        // use `lang\{Type, Primitive as P}` vs. `use lang\Primitive as P;` vs. `use lang\Primitive`
        if (123 === $tokens[$i]->id) {
          $alias= null;
          $group= '';
          for ($i+= 1; $i < $s; $i++) {
            if (44 === $tokens[$i]->id) {
              $imports[$alias ?? $group]= $type.$group;
              $alias= null;
              $group= '';
            } else if (125 === $tokens[$i]->id) {
              $imports[$alias ?? $group]= $type.$group;
              break;
            } else if (T_AS === $tokens[$i]->id) {
              $i+= 2;
              $alias= $tokens[$i]->text;
            } else if (T_WHITESPACE !== $tokens[$i]->id) {
              $group.= $tokens[$i]->text;
            }
          }
        } else if (T_AS === $tokens[$i]->id) {
          $i+= 2;
          $imports[$tokens[$i]->text]= $type;
        } else if (false === ($p= strrpos($type, '\\'))) {
          $imports[$type]= null;
        } else {
          $imports[substr($type, strrpos($type, '\\') + 1)]= $type;
        }

        // Skip over whitespace
        if (T_WHITESPACE === $tokens[$i]->id) $i++;
      } while (44 === $tokens[$i]->id);
    }
    return $imports;
  }

  /**
   * Returns class meta information for a given class
   *
   * @param  string|ReflectionClass|object $class
   * @return [:var]
   */
  public function meta($class) {
    if ($class instanceof ReflectionClass) {
      $reflect= $class;
    } else if (is_object($class)) {
      $reflect= new ReflectionClass($class);
    } else {
      $reflect= new ReflectionClass(strtr($class, '.', '\\'));
    }

    $properties= [];
    foreach ($reflect->getProperties() as $property) {
      $comment= $property->getDocComment() ?: '';
      $type= null;
      if (false !== ($p= strpos($comment, '* @'))) {
        preg_match_all('/@([a-z]+)\s*([^\r\n]+)?/', $comment, $matches, PREG_SET_ORDER, $p + 2);
        foreach ($matches as $match) {
          if ('type' === $match[1]) {
            $type= $this->type($match[2], $reflect);
          }
        }
      }

      $properties[$property->name]= [
        DETAIL_RETURNS => $type,
        DETAIL_COMMENT => $this->comment($comment, $p),
      ];
    }

    $methods= [];
    foreach ($reflect->getMethods() as $method) {
      $comment= $method->getDocComment() ?: '';
      $params= $throws= [];
      $returns= null;

      // Parse doc comment
      if (false !== ($p= strpos($comment, '* @'))) {
        preg_match_all('/@([a-z]+)\s*([^\r\n]+)?/', $comment, $matches, PREG_SET_ORDER, $p + 2);
        foreach ($matches as $match) {
          if ('param' === $match[1]) {
            $params[]= $this->type($match[2], $reflect);
          } else if ('return' === $match[1]) {
            $returns= $this->type($match[2], $reflect);
          } else if ('throws' === $match[1]) {
            $throws[]= $this->type($match[2], $reflect);
          }
        }
      }

      $methods[$method->name]= [
        DETAIL_ARGUMENTS => $params,
        DETAIL_RETURNS   => $returns,
        DETAIL_THROWS    => $throws,
        DETAIL_COMMENT   => $this->comment($comment, $p),
      ];
    }

    // Returns structure compatible with xp::$meta
    return [
      'class' => [DETAIL_COMMENT => $this->comment($reflect->getDocComment() ?: '')],
      $properties,
      $methods,
    ];
  }
}