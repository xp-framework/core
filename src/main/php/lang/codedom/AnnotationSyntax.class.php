<?php namespace lang\codedom;

class AnnotationSyntax extends Syntax {

  static function __static() {
    $number= new AnyOf([
      T_DNUMBER => new Sequence([], function($values) { return (double)$values[0]; }),
      T_LNUMBER => new Sequence([], function($values) { return (int)$values[0]; })
    ]);
    $key= new AnyOf([
      T_STRING      => new Sequence([], function($values) { return $values[0]; }),
      T_RETURN      => new Returns('return'),
      T_CLASS       => new Returns('class'),
      T_IMPLEMENTS  => new Returns('implements'),
      T_LIST        => new Returns('list')
    ]);
    $type= new Tokens([T_STRING, T_NS_SEPARATOR]);

    self::$parse[__CLASS__]= [
      ':start' => new Sequence([new Token(T_OPEN_TAG), new Token('['), new ListOf(new Rule(':annotation')), new Token(']')], function($values) {
        return new Annotations($values[2]);
      }),
      ':annotation' => new Sequence([new Token('@'), new Rule(':target_opt'), new Token(T_STRING), new Rule(':value_opt')], function($values) {
        return new AnnotationDeclaration($values[1], $values[2], $values[3] ?: new AnnotationValue(null));
      }),
      ':target_opt' => new Optional(new Sequence(
        [new Token(T_VARIABLE), new Token(':')],
        function($values) { return substr($values[0], 1); }
      )),
      ':value_opt' => new Optional(new Sequence(
        [new Token('('), new Rule(':expr'), new Token(')')],
        function($values) { return $values[1]; }
      )),
      ':expr' => new AnyOf([
        '-' => new Sequence([$number], function($values) {
          return new AnnotationValue(-1 * $values[1]);
        }),
        '+' => new Sequence([$number], function($values) {
          return new AnnotationValue($values[1]);
        }),
        '[' => new Sequence([new Optional(new ListOf(new Rule(':element'))), new Token(']')], function($values) {
           return new AnnotationArray((array)$values[1]);
        }),
        T_ARRAY => new Sequence([new Token('('), new Optional(new ListOf(new Rule(':element'))), new Token(')')], function($values) {
           return new AnnotationArray((array)$values[2]);
        }),
        T_DNUMBER => new Sequence([], function($values) {
          return new AnnotationValue((double)$values[0]);
        }),
        T_LNUMBER => new Sequence([], function($values) {
          return new AnnotationValue((int)$values[0]);
        }),
        T_CONSTANT_ENCAPSED_STRING => new Sequence([], function($values) {
          return new AnnotationValue(eval('return '.$values[0].';'));
        }),
        T_NEW => new Sequence([$type, new Token('('), new Optional(new ListOf(new Rule(':expr'))), new Token(')')], function($values) {
          return new AnnotationInstance(implode('', $values[1]), (array)$values[3]);
        }),
        T_FUNCTION => new Sequence([new Token('('), new SkipOver('(', ')'), new Token('{'), new SkipOver('{', '}')], function($values) {
          return new AnnotationClosure($values[2], $values[4]);
        }),
        ], [
        new Sequence([$type, new Token(T_DOUBLE_COLON), new Rule(':member')], function($values) {
          return new AnnotationMember(implode('', $values[0]), $values[2]);
        }),
        new Sequence([new ListOf(new Rule(':pair'))], function($values) {
          return new AnnotationPairs($values[0]);
        }),
        new Sequence([new Token(T_STRING)], function($values) {
          return new AnnotationConstant($values[0]);
        })
      ]),
      ':pair'  => new Sequence([$key, new Token('='), new Rule(':expr')], function($values) {
        return [$values[0] => $values[2]];
      }),
      ':element' => new AnyOf([], [
        new Sequence([new Token(T_CONSTANT_ENCAPSED_STRING), new Token(T_DOUBLE_ARROW), new Rule(':expr')], function($values) {
          return [trim($values[0], '"\'') => $values[2]];
        }),
        new Sequence([new Rule(':expr')], function($values) {
          return [0 => $values[0]];
        })
      ]),
      ':member' => new Sequence(
        [new AnyOf([
          T_STRING   => new Sequence([], function($values) { return $values[0]; }),
          T_CLASS    => new Sequence([], function($values) { return $values[0]; }),
          T_VARIABLE => new Sequence([], function($values) { return $values[0]; }),
        ])],
        function($values) { return $values[0]; }
      )
    ];
  }

  /**
   * Parses input
   *
   * @param  string $input
   * @return var
   * @throws lang.FormatException
   */
  public function parse($input) {
    return parent::parse('<?php '.$input);
  }
}