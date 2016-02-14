<?php namespace net\xp_framework\unittest\runtime;

use xp\runtime\Code;

class CodeTest extends \unittest\TestCase {

  #[@test]
  public function can_create() {
    new Code('"Test"');
  }

  #[@test]
  public function can_create_with_empty() {
    new Code('');
  }

  #[@test]
  public function fragment() {
    $this->assertEquals('var_dump("Test");', (new Code('var_dump("Test")'))->fragment());
  }

  #[@test]
  public function fragment_with_semicolon() {
    $this->assertEquals('var_dump("Test");', (new Code('var_dump("Test");'))->fragment());
  }

  #[@test, @values([
  #  '<?php var_dump("Test")', '<?php    var_dump("Test")',
  #  '<? var_dump("Test")', '<?          var_dump("Test")',
  #  '<?=var_dump("Test")', '<?=         var_dump("Test")',
  #  '<?hh var_dump("Test")', '<?hh      var_dump("Test")'
  #])]
  public function fragment_with_php_tag($input) {
    $this->assertEquals('var_dump("Test");', (new Code($input))->fragment());
  }

  #[@test]
  public function expression() {
    $this->assertEquals('return "Test";', (new Code('"Test"'))->expression());
  }

  #[@test]
  public function expression_with_semicolon() {
    $this->assertEquals('return "Test";', (new Code('"Test";'))->expression());
  }

  #[@test]
  public function expression_with_existing_return() {
    $this->assertEquals('return "Test";', (new Code('return "Test";'))->expression());
  }

  #[@test, @values([
  #  'use util\Date; Date::now()',
  #  'use util\Date, util\TimeZone; Date::now()',
  #  'use util\Date; use util\TimeZone; Date::now()',
  #  'use util\{Date, TimeZone}; Date::now()',
  #  ' use util\Date; Date::now()',
  #  '<?php use util\Date; Date::now()',
  #  '<?php  use util\Date; Date::now()',
  #  "<?php\nuse util\Date; Date::now()"
  #])]
  public function use_is_stripped_from_fragment($input) {
    $this->assertEquals('Date::now();', (new Code($input))->fragment());
  }

  #[@test, @values([
  #  'use util\Date; Date::now()',
  #  'use util\Date, util\TimeZone; Date::now()',
  #  'use util\Date; use util\TimeZone; Date::now()',
  #  'use util\{Date, TimeZone}; Date::now()',
  #  ' use util\Date; Date::now()',
  #  '<?php use util\Date; Date::now()',
  #  '<?php  use util\Date; Date::now()',
  #  "<?php\nuse util\Date; Date::now()"
  #])]
  public function use_is_stripped_from_expression($input) {
    $this->assertEquals('return Date::now();', (new Code($input))->expression());
  }

  #[@test]
  public function empty_code_has_no_imports() {
    $this->assertEquals([], (new Code(''))->imports());
  }

  #[@test]
  public function code_without_imports() {
    $this->assertEquals([], (new Code('"Test"'))->imports());
  }

  #[@test, @values([
  #  'use util\Date;',
  #  ' use util\Date;',
  #  '<?php use util\Date;',
  #  '<?php  use util\Date;',
  #  "\nuse util\Date;"
  #])]
  public function code_with_single_import($input) {
    $this->assertEquals(['util\Date'], (new Code($input))->imports());
  }

  #[@test]
  public function code_with_multiple_imports() {
    $this->assertEquals(['util\Date', 'util\TimeZone'], (new Code('use util\Date; use util\TimeZone; "Test"'))->imports());
  }

  #[@test]
  public function code_with_combined_import() {
    $this->assertEquals(['util\Date', 'util\TimeZone'], (new Code('use util\Date, util\TimeZone; "Test"'))->imports());
  }

  #[@test]
  public function code_with_grouped_import() {
    $this->assertEquals(['util\Date', 'util\TimeZone'], (new Code('use util\{Date, TimeZone}; "Test"'))->imports());
  }

  #[@test]
  public function head_with_no_import() {
    $this->assertEquals('', (new Code('"Test"'))->head());
  }

  #[@test]
  public function head_with_single_import() {
    $this->assertEquals('use util\Date;', (new Code('use util\Date; "Test"'))->head());
  }

  #[@test, @values([
  #  'use util\Date, util\TimeZone; Date::now()',
  #  'use util\Date; use util\TimeZone; Date::now()',
  #  'use util\{Date, TimeZone}; Date::now()'
  #])]
  public function head_with_multiple_imports($input) {
    $this->assertEquals('use util\Date, util\TimeZone;', (new Code($input))->head());
  }

  #[@test, @values([
  #  '#!/usr/bin/xp',
  #  '#!/usr/bin/env xp'
  #])]
  public function fragment_with_shebang($variation) {
    $this->assertEquals('exit();', (new Code($variation."\n<?php exit();"))->fragment());
  }
}