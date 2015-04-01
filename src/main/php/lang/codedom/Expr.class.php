<?php namespace lang\codedom;

use lang\FormatException;

class Expr extends Match {
  
  public function consume($rules, $stream, $values) {
    $braces= $array= 0;
    $expr= implode('', $values);
    do {
      $t= $stream->token(true);
      if ('[' === $t) {
        $braces++;
        $expr.= '[';
      } else if ('(' === $t) {
        $array++;
        $expr.= '(';
      } else if (']' === $t) {
        $braces--;
        $expr.= ']';
      } else if (')' === $t) {
        if (--$array < 0) return new Values(trim($expr));
        $expr.= ')';
      } else if ((',' === $t || ';' === $t) && (0 === $braces && 0 === $array)) {
        return new Values(trim($expr));
      } else {
        $expr.= is_array($t) ? $t[1] : $t;
      }
    } while ($stream->next());

    return new Unexpected('End of file', $stream->line());
  }

  public function toString() { return $this->getClassName(); }
}