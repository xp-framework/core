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
    $this->assertEquals('return "Test";', (new Code('"Test"'))->withReturn()->fragment());
  }

  #[@test]
  public function expression_with_semicolon() {
    $this->assertEquals('return "Test";', (new Code('"Test";'))->withReturn()->fragment());
  }

  #[@test]
  public function expression_with_existing_return() {
    $this->assertEquals('return "Test";', (new Code('return "Test";'))->withReturn()->fragment());
  }

  #[@test, @values([
  #  'use util\Date; test()',
  #  'use util\Date, util\TimeZone; test()',
  #  'use util\Date; use util\TimeZone; test()',
  #  'use util\{Date, TimeZone}; test()',
  #  ' use util\Date; test()',
  #  '<?php use util\Date; test()',
  #  '<?php  use util\Date; test()',
  #  "<?php\nuse util\Date; test()"
  #])]
  public function use_is_stripped_from_fragment($input) {
    $this->assertEquals('test();', (new Code($input))->fragment());
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
    $this->assertEquals(['util\Date', 'util\TimeZone'], (new Code('use util\{Date, TimeZone}; test();'))->imports());
  }

  #[@test]
  public function code_with_import_from_module() {
    $this->assertEquals(['util\data\Sequence'], (new Code('use util\data\Sequence from "xp-forge/sequence"'))->imports());
  }

  #[@test]
  public function head_with_no_import() {
    $this->assertEquals('', (new Code('test();'))->head());
  }

  #[@test]
  public function head_with_single_import() {
    $this->assertEquals('use util\Date;', (new Code('use util\Date; test();'))->head());
  }

  #[@test, @values([
  #  'use util\Date, util\TimeZone; test()',
  #  'use util\Date; use util\TimeZone; test()',
  #  'use util\{Date, TimeZone}; test()',
  #  "use util\Date;\nuse util\TimeZone;\ntest()"
  #])]
  public function head_with_multiple_imports($input) {
    $this->assertEquals('use util\Date, util\TimeZone;', (new Code($input))->head());
  }

  #[@test]
  public function head_with_namespace() {
    $this->assertEquals('namespace test;', (new Code('namespace test; test();'))->head());
  }

  #[@test, @values([
  #  '#!/usr/bin/xp',
  #  '#!/usr/bin/env xp'
  #])]
  public function fragment_with_shebang($variation) {
    $this->assertEquals('exit();', (new Code($variation."\n<?php exit();"))->fragment());
  }

  #[@test]
  public function modules_for_code_with_import_without_module() {
    $this->assertEquals([], (new Code('use util\data\Sequence;'))->modules()->all());
  }

  #[@test]
  public function modules_for_code_with_import_from_module() {
    $this->assertEquals(['xp-forge/sequence'], (new Code('use util\data\Sequence from "xp-forge/sequence";'))->modules()->all());
  }

  #[@test, @values([
  #  'return "Test";',
  #  '<?php return "Test";',
  #  "<?php\nreturn 'Test';",
  #  "<?php namespace test;\nreturn 'Test';",
  #])]
  public function run($input) {
    $code= new Code($input);
    $this->assertEquals('Test', $code->run());
  }

  #[@test]
  public function run_without_return() {
    $code= new Code('');
    $this->assertEquals(null, $code->run());
  }

  #[@test]
  public function code_has_access_to_argv() {
    $code= new Code('return $argv;');
    $this->assertEquals([1, 2, 3], $code->run([1, 2, 3]));
  }

  #[@test]
  public function code_has_access_to_argc() {
    $code= new Code('return $argc;');
    $this->assertEquals(3, $code->run([1, 2, 3]));
  }

  #[@test, @values([
  #  ['', 1],
  #  ["<?php\n", 2],
  #  ["<?php namespace test;\n", 2],
  #  ["<?php namespace test;\n\nuse util\cmd\Console;\n\n", 5],
  #])]
  public function errors_reported_with_script_name($head, $line) {
    $code= new Code($head.'trigger_error("Test");', 'test.script.php');
    $code->run();

    $e= ['Test' => ['class' => null, 'method' => 'trigger_error', 'cnt' => 1]];
    try {
      $this->assertEquals(['test.script.php' => [$line => $e]], \xp::$errors);
    } finally {
      \xp::gc();
    }
  }
}