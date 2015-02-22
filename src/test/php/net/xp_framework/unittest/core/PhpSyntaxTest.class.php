<?php namespace net\xp_framework\unittest\core;

use lang\codedom\PhpSyntax;
use lang\codedom\CodeUnit;
use lang\codedom\ClassDeclaration;
use lang\codedom\InterfaceDeclaration;
use lang\codedom\TraitDeclaration;
use lang\codedom\MethodDeclaration;
use lang\codedom\FieldDeclaration;
use lang\codedom\ConstantDeclaration;
use lang\codedom\TraitUsage;

/**
 *
 */
class PhpSyntaxTest extends \unittest\TestCase {

  #[@test]
  public function class_in_global_scope() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php class Test extends Object { }')
    );
  }

  #[@test]
  public function class_in_namespace() {
    $this->assertEquals(
      new CodeUnit('lang', [], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php namespace lang; class Test extends Object { }')
    );
  }

  #[@test]
  public function class_with_import() {
    $this->assertEquals(
      new CodeUnit('lang', ['util\Date'], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php namespace lang; use util\Date; class Test extends Object { }')
    );
  }

  #[@test]
  public function class_with_imports() {
    $this->assertEquals(
      new CodeUnit('lang', ['util\Date', 'util\Objects'], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php namespace lang; use util\Date; use util\Objects; class Test extends Object { }')
    );
  }

  #[@test]
  public function class_with_extension_import() {
    $this->assertEquals(
      new CodeUnit('lang', ['xp\ArrayListExtensions'], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php namespace lang; new import("xp.ArrayListExtensions"); class Test extends Object { }')
    );
  }

  #[@test]
  public function class_without_parent() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', null, [], [])),
      (new PhpSyntax())->parse('<?php class Test { }')
    );
  }

  #[@test]
  public function class_implementing_interface() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', null, ['Generic'], [])),
      (new PhpSyntax())->parse('<?php class Test implements Generic { }')
    );
  }

  #[@test]
  public function class_implementing_interfaces() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', null, ['\lang\Generic', '\Serializable'], [])),
      (new PhpSyntax())->parse('<?php class Test implements \lang\Generic, \Serializable { }')
    );
  }

  #[@test]
  public function class_using_trait() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', null, [], [
        new TraitUsage('Base')
      ])),
      (new PhpSyntax())->parse('<?php class Test { use Base; }')
    );
  }

  #[@test]
  public function class_using_traits() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', null, [], [
        new TraitUsage('Base'),
        new TraitUsage('\util\Observer')
      ])),
      (new PhpSyntax())->parse('<?php class Test { use Base; use \util\Observer; }')
    );
  }

  #[@test]
  public function class_with_abstract_modifier() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(MODIFIER_ABSTRACT, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php abstract class Test extends Object { }')
    );
  }

  #[@test]
  public function class_with_final_modifier() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(MODIFIER_FINAL, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php final class Test extends Object { }')
    );
  }

  #[@test]
  public function class_with_annotation() {
    $this->assertEquals(
      new CodeUnit('test', [], new ClassDeclaration(0, '[@test]', 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php namespace test;
        #[@test]
        class Test extends Object { }
      ')
    );
  }

  #[@test]
  public function class_with_multi_line_annotation() {
    $this->assertEquals(
      new CodeUnit('test', [], new ClassDeclaration(0, '[@test(key = "value",values = [1, 2, 3])]', 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php namespace test;
        #[@test(
        #  key = "value",
        #  values = [1, 2, 3]
        #)]
        class Test extends Object { }
      ')
    );
  }

  #[@test]
  public function class_with_method() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new MethodDeclaration(0, null, 'test', '', null, '')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { function test() { } }')
    );
  }

  #[@test]
  public function class_with_method_with_code() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new MethodDeclaration(0, null, 'test', '', null, 'return true;')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { function test() { return true; } }')
    );
  }

  #[@test]
  public function class_with_method_with_arguments() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new MethodDeclaration(0, null, 'test', '$a= 1, Generic $b, $c= array(1)', null, 'return true;')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { function test($a= 1, Generic $b, $c= array(1)) { return true; } }')
    );
  }

  #[@test]
  public function class_with_field() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new FieldDeclaration(MODIFIER_PUBLIC, null, 'test', null)
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { public $test; }')
    );
  }

  #[@test]
  public function class_with_field_with_int_initial() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new FieldDeclaration(MODIFIER_PUBLIC, null, 'test', '1')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { public $test = 1; }')
    );
  }

  #[@test, @values(['[1, 2, 3]', 'array(1, 2, 3)'])]
  public function class_with_field_with_array_initial($array) {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new FieldDeclaration(MODIFIER_PUBLIC, null, 'test', $array)
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { public $test= '.$array.'; }')
    );
  }

  #[@test]
  public function class_with_fields() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new FieldDeclaration(MODIFIER_PUBLIC, null, 'a', null),
        new FieldDeclaration(MODIFIER_PRIVATE, null, 'b', null),
        new FieldDeclaration(MODIFIER_PROTECTED, null, 'c', null),
        new FieldDeclaration(MODIFIER_STATIC, null, 'd', null)
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { public $a; private $b; protected $c; static $d; }')
    );
  }

  #[@test]
  public function class_with_grouped_fields() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new FieldDeclaration(MODIFIER_PUBLIC, null, 'a', null),
        new FieldDeclaration(0, null, 'b', 'true'),
        new FieldDeclaration(0, null, 'c', null)
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { public $a, $b= true, $c; }')
    );
  }

  #[@test]
  public function class_with_annotated_field() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new FieldDeclaration(MODIFIER_PUBLIC, '[@type("int")]', 'test', null)
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object {
        #[@type("int")]
        public $test;
      }')
    );
  }

  #[@test]
  public function class_with_annotated_grouped_fields() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new FieldDeclaration(MODIFIER_PUBLIC, '[@type("int")]', 'a', null),
        new FieldDeclaration(0, null, 'b', null)
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object {
        public
          #[@type("int")]
          $a,
          $b;
      }')
    );
  }

  #[@test]
  public function class_with_int_const() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new ConstantDeclaration('TEST', '4')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { const TEST = 4; }')
    );
  }

  #[@test]
  public function class_with_string_const() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new ConstantDeclaration('TEST', '"Test"')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { const TEST = "Test"; }')
    );
  }

  #[@test]
  public function class_with_constants() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new ConstantDeclaration('A', '"A"'),
        new ConstantDeclaration('B', '"B"')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { const A = "A"; const B = "B"; }')
    );
  }

  #[@test]
  public function class_with_grouped_constants() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [
        new ConstantDeclaration('A', '"A"'),
        new ConstantDeclaration('B', '"B"')
      ])),
      (new PhpSyntax())->parse('<?php class Test extends Object { const A = "A", B = "B"; }')
    );
  }

  #[@test]
  public function class_with_methods_with_modifiers() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', null, [], [
        new MethodDeclaration(MODIFIER_PUBLIC | MODIFIER_STATIC, null, 'newInstance', '', null, 'return new self();'),
        new MethodDeclaration(MODIFIER_PRIVATE | MODIFIER_FINAL, null, 'create', '', null, ''),
        new MethodDeclaration(MODIFIER_PROTECTED | MODIFIER_ABSTRACT, null, 'arguments', '', null, null)
      ])),
      (new PhpSyntax())->parse('<?php class Test {
        public static function newInstance() { return new self(); }
        private final function create() { }
        protected abstract function arguments();
      }')
    );
  }

  #[@test]
  public function class_with_annotated_method() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', null, [], [
        new MethodDeclaration(MODIFIER_PUBLIC, '[@test]', 'verify', '', null, ''),
      ])),
      (new PhpSyntax())->parse('<?php class Test {
        #[@test]
        public function verify() { }
      }')
    );
  }

  #[@test]
  public function interface_in_global_scope() {
    $this->assertEquals(
      new CodeUnit(null, [], new InterfaceDeclaration(0, null, 'Test', [], [])),
      (new PhpSyntax())->parse('<?php interface Test { }')
    );
  }

  #[@test]
  public function interface_with_parent() {
    $this->assertEquals(
      new CodeUnit(null, [], new InterfaceDeclaration(0, null, 'Test', ['\lang\Runnable'], [])),
      (new PhpSyntax())->parse('<?php interface Test extends \lang\Runnable { }')
    );
  }

  #[@test]
  public function interface_with_parents() {
    $this->assertEquals(
      new CodeUnit(null, [], new InterfaceDeclaration(0, null, 'Test', ['\lang\Runnable', '\Serializable'], [])),
      (new PhpSyntax())->parse('<?php interface Test extends \lang\Runnable, \Serializable { }')
    );
  }

  #[@test]
  public function interface_with_method() {
    $this->assertEquals(
      new CodeUnit(null, [], new InterfaceDeclaration(0, null, 'Test', [], [
        new MethodDeclaration(0, null, 'test', '', null, null)
      ])),
      (new PhpSyntax())->parse('<?php interface Test { function test(); }')
    );
  }

  #[@test]
  public function interface_with_methods() {
    $this->assertEquals(
      new CodeUnit(null, [], new InterfaceDeclaration(0, null, 'Test', [], [
        new MethodDeclaration(0, null, 'a', '', null, null),
        new MethodDeclaration(0, null, 'b', '', null, null)
      ])),
      (new PhpSyntax())->parse('<?php interface Test { function a(); function b(); }')
    );
  }

  #[@test]
  public function interface_with_method_with_modifiers() {
    $this->assertEquals(
      new CodeUnit(null, [], new InterfaceDeclaration(0, null, 'Test', [], [
        new MethodDeclaration(MODIFIER_PUBLIC, null, 'test', '', null, null)
      ])),
      (new PhpSyntax())->parse('<?php interface Test { public function test(); }')
    );
  }

  #[@test]
  public function trait_in_global_scope() {
    $this->assertEquals(
      new CodeUnit(null, [], new TraitDeclaration(0, null, 'Test', [])),
      (new PhpSyntax())->parse('<?php trait Test { }')
    );
  }

  #[@test]
  public function trait_with_method() {
    $this->assertEquals(
      new CodeUnit(null, [], new TraitDeclaration(0, null, 'Test', [
        new MethodDeclaration(MODIFIER_PUBLIC, null, 'test', '', null, '')
      ])),
      (new PhpSyntax())->parse('<?php trait Test { public function test() { } }')
    );
  }

  #[@test]
  public function legacy_defines() {
    $this->assertEquals(
      new CodeUnit(null, [], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php define("CONSTANT", 1); class Test extends Object { }')
    );
  }

  #[@test]
  public function legacy_defines_after_namespace_and_imports() {
    $this->assertEquals(
      new CodeUnit('test', ['lang\Object', 'util\Date'], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php namespace test;
        use lang\Object;
        use util\Date;

        define("A", 1);
        define("B", 2);

        class Test extends Object { }
      ')
    );
  }

  #[@test]
  public function legacy_uses() {
    $this->assertEquals(
      new CodeUnit(null, ['lang\Object'], new ClassDeclaration(0, null, 'Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php uses("lang.Object"); class Test extends Object { }')
    );
  }

  #[@test]
  public function legacy_package() {
    $this->assertEquals(
      new CodeUnit('net\xp_framework', [], new ClassDeclaration(0, null, 'net·xp_framework·Test', 'Object', [], [])),
      (new PhpSyntax())->parse('<?php $package= "net.xp_framework"; class net·xp_framework·Test extends Object { }')
    );
  }
}
