<?php namespace lang\codedom;

class PhpSyntax extends Syntax {

  static function __static() {
    $type= new Tokens([T_STRING, T_NS_SEPARATOR]);
    $modifiers= new Tokens([T_PUBLIC, T_PRIVATE, T_PROTECTED, T_STATIC, T_FINAL, T_ABSTRACT, T_WHITESPACE]);

    self::$parse= [
      ':start' => new Sequence([new Token(T_OPEN_TAG), new Rule(':namespace'), new Rule(':imports'), new Rule(':uses_opt'), new Rule(':declaration')], function($values) {
        $imports= $values[2];
        foreach ($values[3] as $uses) {
          $imports= array_merge($imports, (array)$uses);
        }
        return new CodeUnit($values[1], $imports, $values[4]);
      }),
      ':namespace' => new Optional(new OneOf([
        T_VARIABLE  => new Sequence([new Token('='), new Token(T_CONSTANT_ENCAPSED_STRING), new Token(';')], function($values) {
          return substr($values[2], 1, -1);
        }),
        T_NAMESPACE => new Sequence([$type, new Token(';')], function($values) {
          return strtr(implode('', $values[1]), '\\', '.');
        })
      ])),
      ':imports' => new Repeated(new OneOf([
        T_USE => new Sequence([$type, new Token(';')], function($values) {
          return strtr(implode('', $values[1]), '\\', '.');
        }),
        T_NEW => new Sequence([new Token(T_STRING), new Token('('), new Token(T_CONSTANT_ENCAPSED_STRING), new Token(')'), new Token(';')], function($values) {
          return trim($values[3], '\'"');
        })
      ])),
      ':uses_opt' => new Repeated(new OneOf([
        T_STRING => new Sequence([new Token('('), new SkipOver('(', ')'), new Token(';')], function($values) {
          if ('uses' === $values[0]) {
            return array_map(function($class) { return trim($class, "'\" "); }, explode(',', $values[2]));
          } else {
            return null;
          }
        })
      ])),
      ':declaration' => new Sequence(
        [
          new Rule(':annotations'),
          $modifiers,
          new OneOf([
            T_CLASS     => new Sequence([new Token(T_STRING), new Rule(':class_parent'), new Rule(':class_implements'), new Rule(':type_body')], function($values) {
              return new ClassDeclaration(0, null, $values[1], $values[2], (array)$values[3], $values[4]);
            }),
            T_INTERFACE => new Sequence([new Token(T_STRING), new Rule(':interface_parents'), new Rule(':type_body')], function($values) {
              return new InterfaceDeclaration(0, null, $values[1], (array)$values[2], $values[3]);
            }),
            T_TRAIT => new Sequence([new Token(T_STRING), new Rule(':type_body')], function($values) {
              return new TraitDeclaration(0, null, $values[1], $values[2]);
            })
          ])
        ],
        function($values) {
          $values[2]->annotate($values[0]);
          $values[2]->access(self::modifiers($values[1]));
          return $values[2];
        }
      ),
      ':annotations' => new Optional(new Sequence([new Token(600)], function($values) {
        return $values[0];
      })),
      ':class_parent' => new Optional(
        new Sequence([new Token(T_EXTENDS), $type], function($values) { return implode('', $values[1]); })
      ),
      ':class_implements' => new Optional(
        new Sequence([new Token(T_IMPLEMENTS), new ListOf($type)], function($values) {
          return array_map(function($v) { return implode('', $v); }, $values[1]);
        })
      ),
      ':interface_parents' => new Optional(
        new Sequence([new Token(T_EXTENDS), new ListOf($type)], function($values) {
          return array_map(function($v) { return implode('', $v); }, $values[1]);
        })
      ),
      ':type_body' => new Sequence([new Token('{'), new Repeated(new Rule(':member')), new Token('}')], function($values) {
        $body= ['member' => [], 'trait' => []];
        foreach ($values[1] as $decl) {
          foreach ($decl as $part) {
            $body[$part->type()][]= $part;
          }
        }
        return new TypeBody($body['member'], $body['trait']);
      }),
      ':member' => new EitherOf([
        new Sequence([new Token(T_USE), $type, new Token(';')], function($values) {
          return [new TraitUsage(implode('', $values[1]))];
        }),
        new Sequence([new Token(T_CONST), new ListOf(new Rule(':const')), new Token(';')], function($values) {
          return $values[1];
        }),
        new Sequence(
          [
            new Rule(':annotations'),
            $modifiers,
            new Rule(':annotations'),   // Old way of annotating fields, in combination with grouped syntax
            new OneOf([
              T_FUNCTION => new Sequence([new Token(T_STRING), new Token('('), new SkipOver('(', ')'), new Rule(':method')], function($values, $stream) {
                $details= self::details($stream->comment());
                if ('__construct' === $values[1]) {
                  return new ConstructorDeclaration(0, null, $values[1], $details['args'], $details['throws'], $values[4]);
                } else {
                  return new MethodDeclaration(0, null, $values[1], $details['args'], $details['returns'], $details['throws'], $values[4]);
                }
              }),
              T_VARIABLE => new Sequence([new Rule(':field')], function($values) {
                return new FieldDeclaration(0, null, substr($values[0], 1), $values[1]);
              })
            ])
          ],
          function($values) {
            $values[0] && $values[3]->annotate($values[0]);
            $values[2] && $values[3]->annotate($values[2]);
            $values[3]->access(self::modifiers($values[1]));
            return [$values[3]];
          }
        ),
      ]),
      ':const' => new Sequence([new Token(T_STRING), new Token('='), new Expr()], function($values) {
        return new ConstantDeclaration($values[0], $values[2]);
      }),
      ':field' => new Sequence(
        [
          new Optional(new Sequence([new Token('='), new Expr()], function($values) { return $values[1]; })),
          new OneOf([';' => new Returns(null), ',' => new Returns(null)])
        ],
        function($values) { return $values[0]; }
      ),
      ':method' => new OneOf([
        ';' => new Returns(null),
        '{' => new Sequence([new SkipOver('{', '}')], function($values) { return $values[1]; })
      ])
    ];
  }

  /**
   * Parses modifier names into flags
   *
   * @param  string[] $names
   * @return int
   */
  private static function modifiers($names) {
    static $modifiers= [
      'public'    => MODIFIER_PUBLIC,
      'private'   => MODIFIER_PRIVATE,
      'protected' => MODIFIER_PROTECTED,
      'static'    => MODIFIER_STATIC,
      'final'     => MODIFIER_FINAL,
      'abstract'  => MODIFIER_ABSTRACT
    ];

    $m= 0;
    foreach ($names as $name) {
      isset($modifiers[$name]) && $m |= $modifiers[$name];
    }
    return $m;
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
  private static function matching($text, $open, $close) {
    for ($braces= $open.$close, $i= 0, $b= 0, $s= strlen($text); $i < $s; $i+= strcspn($text, $braces, $i)) {
      if ($text{$i} === $open) {
        $b++;
      } else if ($text{$i} === $close) {
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
   * @return string
   */
  private static function typeIn($text) {
    if (0 === strncmp($text, 'function(', 9)) {
      $p= self::matching($text, '(', ')');
      $p+= strspn($text, ': ', $p);
      return substr($text, 0, $p).self::typeIn(substr($text, $p));
    } else if (strstr($text, '<')) {
      $p= self::matching($text, '<', '>');
      return substr($text, 0, $p);
    } else {
      return substr($text, 0, strcspn($text, ' '));
    }
  }

  /**
   * Parses methods' api doc comments
   *
   * @param  string $comment
   * @return [:var]
   */
  private static function details($comment) {
    $matches= null;
    preg_match_all('/@([a-z]+)\s*([^\r\n]+)?/', $comment, $matches, PREG_SET_ORDER);

    $arg= 0;
    $details= ['args' => [], 'returns' => 'var', 'throws' => []];
    foreach ($matches as $match) {
      if ('param' === $match[1]) {
        $details['args'][$arg++]= self::typeIn($match[2]);
      } else if ('return' === $match[1]) {
        $details['returns']= self::typeIn($match[2]);
      } else if ('throws' === $match[1]) {
        $details['throws'][]= self::typeIn($match[2]);
      }
    }
    return $details;
  }
}