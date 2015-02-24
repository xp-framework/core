<?php namespace lang\reflect;

use lang\XPException;
use lang\codedom\AnnotationSyntax;
use lang\codedom\PhpSyntax;

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
   * Parse details from a given input string
   *
   * @param   string $bytes
   * @param   string $context
   * @param   [:string] $imports
   * @return  [:var] details
   */
  public function parseAnnotations($bytes, $context, $imports= []) {
    $annotations= [0 => [], 1 => []];
    if (null === $bytes) return $annotations;

    try {
      $parsed= (new AnnotationSyntax())->parse(trim($bytes, "# \t\n\r"))->resolve($context, $imports);
    } catch (XPException $e) {
      raise('lang.ClassFormatException', $e->getMessage().' in '.$context, $e);
    }

    isset($parsed[null]) && $annotations[0]= $parsed[null];
    foreach ($parsed as $key => $value) {
      $key && $annotations[1]['$'.$key]= $value;
    }
    return $annotations;
  }

  /**
   * Parse details from a given input string
   *
   * @param   string bytes
   * @param   string context default ''
   * @return  [:var] details
   */
  public function parseDetails($bytes, $context= '') {
    $codeunit= (new PhpSyntax())->parse(ltrim($bytes));

    $imports= [];
    foreach ($codeunit->imports() as $type) {
      $imports[substr($type, strrpos($type, '.')+ 1)]= $type;
    }

    $decl= $codeunit->declaration();
    $annotations= $this->parseAnnotations($decl->annotations(), $context, $imports, -1);
    $details= [0 => [], 1 => [], 'class' => [DETAIL_ANNOTATIONS  => $annotations[0]]];

    foreach ($decl->body()->members() as $member) {
      $annotations= $this->parseAnnotations($member->annotations(), $context, $imports);
      if ($member->isField()) {
        $details[0][$member->name()]= [DETAIL_ANNOTATIONS => $annotations[0]];
      } else if ($member->isMethod()) {
        $details[1][$member->name()]= [
          DETAIL_ARGUMENTS    => $member->parameters(),
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