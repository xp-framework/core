<?php namespace net\xp_framework\unittest\annotations;

use lang\{XPClass, DynamicClassLoader, ElementNotFoundException};
use unittest\TestCase;
use unittest\actions\RuntimeVersion;

#[@action(new RuntimeVersion('>=8.0.0-dev'))]
class AttributesTest extends TestCase {
  private $cl;

  /** @return void */
  public function setUp() {
    $this->cl= DynamicClassLoader::instanceFor(self::class);
  }

  /**
   * Declares a type from source
   *
   * @param  string $source
   * @param  ?string $namespace Optional namespace name
   * @return lang.XPClass
   */
  private function type($source, $namespace= null) {
    static $u= 0;

    $name= '__A'.(++$u);
    if (null === $namespace) {
      $this->cl->setClassBytes($name, sprintf($source, $name));
      return $this->cl->loadClass($name);
    } else {
      $qualified= $namespace.'.'.$name;
      $this->cl->setClassBytes($qualified, 'namespace '.$namespace.';'.sprintf($source, $name));
      return $this->cl->loadClass($qualified);
    }
  }

  /**
   * Assertion helper
   *
   * @param  var $expected
   * @param  lang.XPClass|lang.reflect.Method|lang.reflect.Field|lang.reflect.Parameter $annotated
   * @throws unittest.AssertionFailedError
   */
  private function assertAnnotations($expected, $annotated) {
    if (null === $expected) {
      $this->assertFalse($annotated->hasAnnotations(), 'hasAnnotations');
      $this->assertEquals([], $annotated->getAnnotations());
      $this->assertFalse($annotated->hasAnnotation('Test'), 'hasAnnotation("Test")');
      try {
        $annotated->getAnnotation('Test');
        $this->fail('Exception not raised', null, ElementNotFoundException::class);
      } catch (ElementNotFoundException $expected) {
        // OK
      }
    } else {
      $this->assertTrue($annotated->hasAnnotations(), 'hasAnnotations');
      $this->assertEquals($expected, $annotated->getAnnotations());
      foreach ($expected as $name => $value) {
        $this->assertTrue($annotated->hasAnnotation($name), 'hasAnnotation("'.$name.'")');
        $this->assertEquals($value, $annotated->getAnnotation($name));
      }
    }
  }

  #[@test]
  public function on_class() {
    $t= $this->type('<<Test>> class %s { }');
    $this->assertAnnotations(['Test' => null], $t);
  }

  #[@test]
  public function on_field() {
    $t= $this->type('class %s { <<Test>> public $fixture; }');
    $this->assertAnnotations(['Test' => null], $t->getField('fixture'));
  }

  #[@test]
  public function on_method() {
    $t= $this->type('class %s { <<Test>> public function fixture() { } }');
    $this->assertAnnotations(['Test' => null], $t->getMethod('fixture'));
  }

  #[@test]
  public function on_parameter() {
    $t= $this->type('class %s { public function fixture(<<Test>> $param) { } }');
    $this->assertAnnotations(['Test' => null], $t->getMethod('fixture')->getParameter(0));
  }

  #[@test]
  public function no_class_annotations() {
    $t= $this->type('class %s { }');
    $this->assertAnnotations(null, $t);
  }

  #[@test]
  public function no_field_annotations() {
    $t= $this->type('class %s { public $fixture; }');
    $this->assertAnnotations(null, $t->getField('fixture'));
  }

  #[@test]
  public function no_method_annotations() {
    $t= $this->type('class %s { public function fixture() { } }');
    $this->assertAnnotations(null, $t->getMethod('fixture'));
  }

  #[@test]
  public function no_parameter_annotations() {
    $t= $this->type('class %s { public function fixture($param) { } }');
    $this->assertAnnotations(null, $t->getMethod('fixture')->getParameter(0));
  }

  #[@test]
  public function inside_namespace() {
    $t= $this->type('<<Test>> class %s { }', 'com\\example');
    $this->assertAnnotations(['com\\example\\Test' => null], $t);
  }

  #[@test]
  public function resolved_against_imports_inside_namespace() {
    $t= $this->type('use unittest\\Test; <<Test>> class %s { }', 'com\\example');
    $this->assertAnnotations(['unittest\\Test' => null], $t);
  }

  #[@test]
  public function qualified() {
    $t= $this->type('<<annotations\\Test>> class %s { }', 'unittest');
    $this->assertAnnotations(['unittest\\annotations\\Test' => null], $t);
  }

  #[@test]
  public function fully_qualified() {
    $t= $this->type('<<\\unittest\\annotations\\Test>> class %s { }', 'com\\example');
    $this->assertAnnotations(['unittest\\annotations\\Test' => null], $t);
  }

  #[@test]
  public function lowercase_annotations_are_not_resolved() {
    $t= $this->type('<<test>> class %s { }', 'com\\example');
    $this->assertAnnotations(['test' => null], $t);
  }

  #[@test]
  public function with_value() {
    $t= $this->type('<<Author("Test")>> class %s { }');
    $this->assertAnnotations(['Author' => 'Test'], $t);
  }

  #[@test]
  public function with_values() {
    $t= $this->type('<<Product("PHP", "8.0.0")>> class %s { }');
    $this->assertAnnotations(['Product' => ['PHP', '8.0.0']], $t);
  }

  #[@test, @values([
  #  'Values',
  #  'unittest\\Values',
  #  '\\unittest\\Values',
  #])]
  public function with_non_static_value_inline($annotation) {
    $t= $this->type('
      use net\\xp_framework\\unittest\\annotations\\Name;
      use unittest\\{Test, Values, Expect};
      use lang\\ElementNotFoundException;

      class %s {

        <<Test>>
        <<'.$annotation.'(
          #[
          #  new Name("A"),
          #  new Name("B"),
          #]
        )>>
        <<Expect(
          #fn($e) => $e instanceof ElementNotFoundException && strstr($e->getMessage(), "fail");
        )>>
        public function fixture($value) {
          // TBI
        }
      }
    ');

    $m= $t->getMethod('fixture');
    $this->assertNull($m->getAnnotation('unittest\\Test'));
    $this->assertEquals([new Name('A'), new Name('B')], $m->getAnnotation('unittest\\Values'));
    $this->assertTrue($m->getAnnotation('unittest\\Expect')(new ElementNotFoundException('Test failed')));
  }
}
