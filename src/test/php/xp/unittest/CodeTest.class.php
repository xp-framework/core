<?php namespace xp\unittest;

use test\{Assert, Test, Values};
use xp\runtime\Code;

class CodeTest {

  #[Test]
  public function can_create() {
    new Code('"Test"');
  }

  #[Test]
  public function can_create_with_empty() {
    new Code('');
  }

  #[Test]
  public function fragment() {
    Assert::equals('var_dump("Test");', (new Code('var_dump("Test")'))->fragment());
  }

  #[Test]
  public function fragment_with_semicolon() {
    Assert::equals('var_dump("Test");', (new Code('var_dump("Test");'))->fragment());
  }

  #[Test, Values(['<?php var_dump("Test")', '<?php    var_dump("Test")', '<? var_dump("Test")', '<?          var_dump("Test")', '<?=var_dump("Test")', '<?=         var_dump("Test")', '<?hh var_dump("Test")', '<?hh      var_dump("Test")'])]
  public function fragment_with_php_tag($input) {
    Assert::equals('var_dump("Test");', (new Code($input))->fragment());
  }

  #[Test]
  public function expression() {
    Assert::equals('return "Test";', (new Code('"Test"'))->withReturn()->fragment());
  }

  #[Test]
  public function expression_with_semicolon() {
    Assert::equals('return "Test";', (new Code('"Test";'))->withReturn()->fragment());
  }

  #[Test]
  public function expression_with_existing_return() {
    Assert::equals('return "Test";', (new Code('return "Test";'))->withReturn()->fragment());
  }

  #[Test, Values(['use util\Date; test()', 'use util\Date, util\TimeZone; test()', 'use util\Date; use util\TimeZone; test()', 'use util\{Date, TimeZone}; test()', ' use util\Date; test()', '<?php use util\Date; test()', '<?php  use util\Date; test()', "<?php\nuse util\Date; test()"])]
  public function use_is_stripped_from_fragment($input) {
    Assert::equals('test();', (new Code($input))->fragment());
  }

  #[Test]
  public function empty_code_has_no_imports() {
    Assert::equals([], (new Code(''))->imports());
  }

  #[Test]
  public function code_without_imports() {
    Assert::equals([], (new Code('"Test"'))->imports());
  }

  #[Test, Values(['use util\Date;', ' use util\Date;', '<?php use util\Date;', '<?php  use util\Date;', "\nuse util\Date;"])]
  public function code_with_single_import($input) {
    Assert::equals(['util\Date'], (new Code($input))->imports());
  }

  #[Test]
  public function code_with_multiple_imports() {
    Assert::equals(['util\Date', 'util\TimeZone'], (new Code('use util\Date; use util\TimeZone; "Test"'))->imports());
  }

  #[Test]
  public function code_with_combined_import() {
    Assert::equals(['util\Date', 'util\TimeZone'], (new Code('use util\Date, util\TimeZone; "Test"'))->imports());
  }

  #[Test]
  public function code_with_grouped_import() {
    Assert::equals(['util\Date', 'util\TimeZone'], (new Code('use util\{Date, TimeZone}; test();'))->imports());
  }

  #[Test]
  public function code_with_import_from_module() {
    Assert::equals(['util\data\Sequence'], (new Code('use util\data\Sequence from "xp-forge/sequence"'))->imports());
  }

  #[Test]
  public function head_with_no_import() {
    Assert::equals('', (new Code('test();'))->head());
  }

  #[Test]
  public function head_with_single_import() {
    Assert::equals('use util\Date;', (new Code('use util\Date; test();'))->head());
  }

  #[Test, Values(['use util\Date, util\TimeZone; test()', 'use util\Date; use util\TimeZone; test()', 'use util\{Date, TimeZone}; test()', "use util\Date;\nuse util\TimeZone;\ntest()"])]
  public function head_with_multiple_imports($input) {
    Assert::equals('use util\Date, util\TimeZone;', (new Code($input))->head());
  }

  #[Test]
  public function head_with_namespace() {
    Assert::equals('namespace test;', (new Code('namespace test; test();'))->head());
  }

  #[Test, Values(['#!/usr/bin/xp', '#!/usr/bin/env xp'])]
  public function fragment_with_shebang($variation) {
    Assert::equals('exit();', (new Code($variation."\n<?php exit();"))->fragment());
  }

  #[Test]
  public function modules_for_code_with_import_without_module() {
    Assert::equals([], (new Code('use util\data\Sequence;'))->modules()->all());
  }

  #[Test]
  public function modules_for_code_with_import_from_module() {
    Assert::equals(
      ['xp-forge/sequence' => null],
      (new Code('use util\data\Sequence from "xp-forge/sequence";'))->modules()->all()
    );
  }

  #[Test]
  public function modules_for_code_with_import_from_module_with_version() {
    Assert::equals(
      ['xp-forge/sequence' => '^8.0'],
      (new Code('use util\data\Sequence from "xp-forge/sequence@^8.0";'))->modules()->all()
    );
  }

  #[Test, Values(['return "Test";', '<?php return "Test";', "<?php\nreturn 'Test';", "<?php namespace test;\nreturn 'Test';",])]
  public function run($input) {
    $code= new Code($input);
    Assert::equals('Test', $code->run());
  }

  #[Test]
  public function run_without_return() {
    $code= new Code('');
    Assert::equals(null, $code->run());
  }

  #[Test]
  public function code_has_access_to_argv() {
    $code= new Code('return $argv;');
    Assert::equals([1, 2, 3], $code->run([1, 2, 3]));
  }

  #[Test]
  public function code_has_access_to_argc() {
    $code= new Code('return $argc;');
    Assert::equals(3, $code->run([1, 2, 3]));
  }

  #[Test, Values([['', 1], ["<?php\n", 2], ["<?php namespace test;\n", 2], ["<?php namespace test;\n\nuse util\cmd\Console;\n\n", 5],])]
  public function errors_reported_with_script_name($head, $line) {
    $code= new Code($head.'trigger_error("Test");', 'test.script.php');
    $code->run();

    try {
      Assert::equals('Test', key(\xp::$errors['test.script.php'][$line]));
    } finally {
      \xp::gc();
    }
  }
}