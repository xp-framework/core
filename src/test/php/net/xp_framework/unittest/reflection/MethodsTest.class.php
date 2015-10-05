<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Object;
use lang\Value;
use lang\ClassLoader;
use lang\Error;
use lang\Type;
use lang\ArrayType;
use lang\MapType;
use lang\Primitive;
use lang\ElementNotFoundException;
use lang\IllegalAccessException;
use lang\IllegalArgumentException;
use lang\reflect\Method;
use lang\reflect\TargetInvocationException;
use unittest\actions\RuntimeVersion;

class MethodsTest extends \unittest\TestCase {
  private static $fixtures= [];

  /**
   * Defines an anonymous type
   *
   * @param  string $decl Type declaration
   * @param  int $modifiers
   * @return lang.XPClass
   */
  private function type($decl= null, $modifiers= '') {
    if (!isset(self::$fixtures[$decl])) {
      $definition= [
        'modifiers'  => $modifiers,
        'kind'       => 'class',
        'extends'    => [Object::class],
        'implements' => [],
        'use'        => []
      ];
      self::$fixtures[$decl]= ClassLoader::defineType(self::class.sizeof(self::$fixtures), $definition, $decl);
    }
    return self::$fixtures[$decl];
  }

  /**
   * Defines a method inside an anonymous type
   *
   * @param  string $decl Method declaration
   * @param  int $modifiers
   * @return lang.reflect.Method
   */
  private function method($decl, $modifiers= '') {
    return $this->type('{ '.$decl.' }', $modifiers)->getMethod('fixture');
  }

  #[@test]
  public function methods_contains_equals_from_Object() {
    $fixture= $this->type();
    $equals= $fixture->getMethod('equals');
    foreach ($fixture->getMethods() as $method) {
      if ($equals->equals($method)) return;
    }
    $this->fail('Equals method not contained', null, $fixture->getMethods());
  }

  #[@test]
  public function declared_methods_does_not_contain_hashCode_from_Object() {
    $fixture= $this->type();
    $equals= $fixture->getMethod('equals');
    foreach ($fixture->getDeclaredMethods() as $method) {
      if ($equals->equals($method)) $this->fail('Equals method contained', null, $fixture->getDeclaredMethods());
    }
  }
  
  #[@test]
  public function declaring_class() {
    $fixture= $this->type('{ public function declared() { }}');
    $this->assertEquals($fixture, $fixture->getMethod('declared')->getDeclaringClass());
  }

  #[@test]
  public function declaring_class_of_inherited_method() {
    $fixture= $this->type();
    $this->assertEquals($fixture->getParentclass(), $fixture->getMethod('equals')->getDeclaringClass());
  }

  #[@test]
  public function has_method_for_existant() {
    $this->assertTrue($this->type('{ public function declared() { }}')->hasMethod('declared'));
  }

  #[@test]
  public function has_method_for_non_existant() {
    $this->assertFalse($this->type()->hasMethod('@@nonexistant@@'));
  }

  #[@test, @values(['__construct', '__destruct', '__static', '__import'])]
  public function has_method_for_special($named) {
    $this->assertFalse($this->type()->hasMethod($named));
  }

  #[@test]
  public function get_existant_method() {
    $this->assertInstanceOf(Method::class, $this->type('{ public function declared() { }}')->getMethod('declared'));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function get_non_existant_method() {
    $this->type()->getMethod('@@nonexistant@@');
  }
  
  #[@test, @expect(ElementNotFoundException::class), @values(['__construct', '__destruct', '__static', '__import'])]
  public function get_method_for_special($named) {
    $this->type()->getMethod($named);
  }

  #[@test]
  public function name() {
    $this->assertEquals('fixture', $this->method('public function fixture() { }')->getName());
  }

  #[@test]
  public function public_modifiers() {
    $this->assertEquals(MODIFIER_PUBLIC, $this->method('public function fixture() { }')->getModifiers());
  }

  #[@test]
  public function private_modifiers() {
    $this->assertEquals(MODIFIER_PRIVATE, $this->method('private function fixture() { }')->getModifiers());
  }

  #[@test]
  public function protected_modifiers() {
    $this->assertEquals(MODIFIER_PROTECTED, $this->method('protected function fixture() { }')->getModifiers());
  }

  #[@test]
  public function final_modifiers() {
    $this->assertEquals(MODIFIER_FINAL | MODIFIER_PUBLIC, $this->method('public final function fixture() { }')->getModifiers());
  }

  #[@test]
  public function static_modifiers() {
    $this->assertEquals(MODIFIER_STATIC | MODIFIER_PUBLIC, $this->method('public static function fixture() { }')->getModifiers());
  }

  #[@test]
  public function abstract_modifiers() {
    $this->assertEquals(MODIFIER_ABSTRACT | MODIFIER_PUBLIC, $this->method('public abstract function fixture();', 'abstract')->getModifiers());
  }

  #[@test]
  public function parameter_type_defaults_to_var() {
    $this->assertEquals(Type::$VAR, $this->method('public function fixture($param) { }')->getParameter(0)->getType());
  }

  #[@test]
  public function no_parameters() {
    $this->assertEquals(0, $this->method('public function fixture() { }')->numParameters());
  }

  #[@test]
  public function one_parameter() {
    $this->assertEquals(1, $this->method('public function fixture($param) { }')->numParameters());
  }

  #[@test]
  public function two_parameters() {
    $this->assertEquals(2, $this->method('public function fixture($a, $b) { }')->numParameters());
  }

  #[@test]
  public function with_comment() {
    $this->assertEquals('Test', $this->method('/** Test */ public function fixture() { }')->getComment());
  }

  #[@test]
  public function without_comment() {
    $this->assertEquals('', $this->method('public function fixture() { }')->getComment());
  }

  #[@test, @values([
  #  ['/** @param var */', Type::$VAR],
  #  ['/** @param bool */', Primitive::$BOOL],
  #  ['/** @param string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @param [:int] */', new MapType(Primitive::$INT)],
  #  ['/** @param lang.Object */', new XPClass(Object::class)]
  #])]
  public function parameter_type_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture($param) { }')->getParameter(0)->getType());
  }

  #[@test, @values([
  #  ['\lang\Value', new XPClass(Value::class)],
  #  ['\lang\Object', new XPClass(Object::class)]
  #])]
  public function parameter_type_determined_via_syntax($literal, $type) {
    $this->assertEquals($type, $this->method('public function fixture('.$literal.' $param) { }')->getParameter(0)->getType());
  }

  #[@test]
  public function self_parameter_type() {
    $fixture= $this->type('{ public function fixture(self $param) { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getParameter(0)->getType());
  }

  #[@test]
  public function return_type_defaults_to_var() {
    $this->assertEquals(Type::$VAR, $this->method('public function fixture() { }')->getReturnType());
  }

  #[@test]
  public function return_type_inherited() {
    $this->assertEquals(Primitive::$BOOL, $this->type()->getMethod('equals')->getReturnType());
  }

  #[@test, @values([
  #  ['/** @return void */', Type::$VOID],
  #  ['/** @return var */', Type::$VAR],
  #  ['/** @return bool */', Primitive::$BOOL],
  #  ['/** @return string[] */', new ArrayType(Primitive::$STRING)],
  #  ['/** @return [:int] */', new MapType(Primitive::$INT)],
  #  ['/** @return lang.Object */', new XPClass(Object::class)]
  #])]
  public function return_type_determined_via_apidoc($apidoc, $type) {
    $this->assertEquals($type, $this->method($apidoc.' public function fixture() { }')->getReturnType());
  }

  #[@test, @ignore('No reflection support yet'), @action(new RuntimeVersion('>=7.0')), @values([
  #  ['string', Primitive::$STRING],
  #  ['array', Type::$ARRAY],
  #  ['\lang\Object', new XPClass(Object::class)]
  #])]
  public function return_type_determined_via_syntax($literal, $type) {
    $this->assertEquals($type, $this->method('public function fixture(): '.$literal.' { }')->getReturnType());
  }

  #[@test]
  public function self_return_type() {
    $fixture= $this->type('{ /** @return self */ public function fixture() { } }');
    $this->assertEquals($fixture, $fixture->getMethod('fixture')->getReturnType());
  }

  #[@test]
  public function invoke_instance() {
    $fixture= $this->type('{ public function fixture() { return "Test"; } }');
    $this->assertEquals('Test', $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []));
  }

  #[@test]
  public function invoke_static() {
    $fixture= $this->type('{ public static function fixture() { return "Test"; } }');
    $this->assertEquals('Test', $fixture->getMethod('fixture')->invoke(null, []));
  }

  #[@test]
  public function invoke_passes_arguments() {
    $fixture= $this->type('{ public function fixture($a, $b) { return $a + $b; } }');
    $this->assertEquals(3, $fixture->getMethod('fixture')->invoke($fixture->newInstance(), [1, 2]));
  }

  #[@test]
  public function invoke_method_without_return() {
    $fixture= $this->type('{ public function fixture() { } }');
    $this->assertNull($fixture->getMethod('fixture')->invoke($fixture->newInstance(), []));
  }

  #[@test, @expect(TargetInvocationException::class)]
  public function cannot_invoke_instance_method_without_object() {
    $fixture= $this->type('{ public function fixture() { } }');
    $fixture->getMethod('fixture')->invoke(null, []);
  }

  #[@test, @expect(TargetInvocationException::class)]
  public function exceptions_raised_during_invocation_are_wrapped() {
    $fixture= $this->type('{ public function fixture() { throw new \lang\IllegalAccessException("Test"); } }');
    $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []);
  }

  #[@test, @expect(TargetInvocationException::class), @action(new RuntimeVersion('>=7.0'))]
  public function exceptions_raised_for_return_type_violations() {
    $fixture= $this->type('{ public function fixture(): array { return null; } }');
    $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []);
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function cannot_invoke_instance_method_with_incompatible() {
    $fixture= $this->type('{ public function fixture() { } }');
    $fixture->getMethod('fixture')->invoke($this, []);
  }

  #[@test, @expect(IllegalAccessException::class), @values([
  #  ['{ private function fixture() { } }'],
  #  ['{ protected function fixture() { } }'],
  #  ['{ public abstract function fixture(); }', 'abstract'],
  #])]
  public function cannot_invoke_non_public($declaration, $modifiers= '') {
    $fixture= $this->type($declaration, $modifiers);
    $fixture->getMethod('fixture')->invoke($fixture->newInstance(), []);
  }

  #[@test, @values([
  #  ['{ private function fixture() { return "Test"; } }'],
  #  ['{ protected function fixture() { return "Test"; } }'],
  #])]
  public function can_invoke_private_or_protected_via_setAccessible($declaration) {
    $fixture= $this->type($declaration);
    $this->assertEquals('Test', $fixture->getMethod('fixture')->setAccessible(true)->invoke($fixture->newInstance(), []));
  }

  #[@test]
  public function has_annotations_when_absent() {
    $this->assertFalse($this->method('public function fixture() { }')->hasAnnotations());
  }

  #[@test]
  public function has_annotations_when_present() {
    $this->assertTrue($this->method("#[@test]\npublic function fixture() { }")->hasAnnotations());
  }

  #[@test]
  public function annotations_are_empty_by_default() {
    $this->assertEquals([], $this->method('public function fixture() { }')->getAnnotations());
  }

  #[@test]
  public function test_annotation() {
    $this->assertEquals(
      ['test' => null],
      $this->method("#[@test]\npublic function fixture() { }")->getAnnotations()
    );
  }

  #[@test]
  public function two_annotations() {
    $this->assertEquals(
      ['test' => null, 'limit' => 20],
      $this->method("#[@test, @limit(20)]\npublic function fixture() { }")->getAnnotations()
    );
  }

  #[@test]
  public function has_annotation_when_absent() {
    $this->assertFalse($this->method('public function fixture() { }')->hasAnnotation('test'));
  }

  #[@test]
  public function has_annotation_for_existant_annotation() {
    $this->assertTrue($this->method("#[@test]\npublic function fixture() { }")->hasAnnotation('test'));
  }

  #[@test]
  public function has_annotation_for_non_existant_annotation() {
    $this->assertFalse($this->method("#[@test]\npublic function fixture() { }")->hasAnnotation('@@nonexistant@@'));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function get_annotation_when_absent() {
    $this->method('public function fixture() { }')->getAnnotation('test');
  }

  #[@test]
  public function get_annotation_for_existant_annotation() {
    $this->assertNull($this->method("#[@test]\npublic function fixture() { }")->getAnnotation('test'));
  }

  #[@test]
  public function get_annotation_for_existant_annotation_with_value() {
    $this->assertEquals(20, $this->method("#[@limit(20)]\npublic function fixture() { }")->getAnnotation('limit'));
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function get_annotation_for_non_existant_annotation() {
    $this->method("#[@test]\npublic function fixture() { }")->getAnnotation('@@nonexistant@@');
  }

  #[@test]
  public function thrown_exceptions_are_empty_by_default() {
    $this->assertEquals([], $this->method('public function fixture() { }')->getExceptionTypes());
  }

  #[@test]
  public function thrown_exception_via_compact_apidoc() {
    $this->assertEquals(
      [new XPClass(IllegalAccessException::class)],
      $this->method('/** @throws lang.IllegalAccessException */ public function fixture() { }')->getExceptionTypes()
    );
  }

  #[@test]
  public function thrown_exceptions_via_apidoc() {
    $this->assertEquals(
      [new XPClass(IllegalAccessException::class), new XPClass(IllegalArgumentException::class)],
      $this->method('
        /**
         * @throws lang.IllegalAccessException
         * @throws lang.IllegalArgumentException
         */
        public function fixture() { }
      ')->getExceptionTypes()
    );
  }

  #[@test]
  public function equality() {
    $fixture= $this->type('{ public function hashCode() { } }');
    $this->assertTrue($fixture->getMethod('hashCode')->equals($fixture->getMethod('hashCode')));
  }
  #[@test]
  public function a_method_is_not_equal_to_parent_method() {
    $fixture= $this->type('{ public function hashCode() { } }');
    $this->assertFalse($fixture->getMethod('hashCode')->equals($fixture->getParentclass()->getMethod('hashCode')));
  }

  #[@test]
  public function a_method_is_not_equal_to_null() {
    $this->assertFalse($this->method('public function fixture() { }')->equals(null));
  }

  #[@test, @values([
  #  ['public function fixture() { }', 'public var fixture()'],
  #  ['private function fixture() { }', 'private var fixture()'],
  #  ['protected function fixture() { }', 'protected var fixture()'],
  #  ['static function fixture() { }', 'public static var fixture()'],
  #  ['private static function fixture() { }', 'private static var fixture()'],
  #  ['protected static function fixture() { }', 'protected static var fixture()'],
  #  ['public function fixture($param) { }', 'public var fixture(var $param)'],
  #  ['/** @return void */ public function fixture() { }', 'public void fixture()'],
  #  ['/** @param string[] */ public function fixture($param) { }', 'public var fixture(string[] $param)'],
  #  ['/** @throws lang.IllegalAccessException */ public function fixture() { }', 'public var fixture() throws lang.IllegalAccessException']
  #])]
  public function string_representation($declaration, $expected) {
    $this->assertEquals($expected, $this->method($declaration)->toString());
  }
}