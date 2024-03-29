<?php namespace lang\unittest;

use lang\ClassFormatException;
use lang\reflect\ClassParser;
use test\{Assert, Call, Expect, Fixture, Test, Value, Values};

class ClassDetailsTest {

  /**
   * Helper method that parses an apidoc comment and returns the matches
   *
   * @param   string comment
   * @return  [:string[]] matches
   * @throws  unittest.AssertionFailedError
   */
  protected function parseComment($comment) {
    $details= (new ClassParser())->parseDetails('
      <?php
        class Test {
          '.$comment.'
          public function test() { }
        }
      ?>',
      self::class
    );
    return $details[1]['test'];
  }
  
  #[Test, Values(['class', 'interface', 'trait'])]
  public function parses($kind) {
    $details= (new ClassParser())->parseDetails('<?php '.$kind.' Test { }');
    Assert::equals(
      [DETAIL_COMMENT => '', DETAIL_ANNOTATIONS => [], DETAIL_TARGET_ANNO  => []],
      $details['class']
    );
  }

  #[Test]
  public function commentString() {
    $details= $this->parseComment('
      /**
       * A protected method
       *
       * Note: Not compatible with PHP 4.1.2!
       *
       * @param   string param1
       */
    ');
    Assert::equals(
      "A protected method\n\nNote: Not compatible with PHP 4.1.2!",
      $details[DETAIL_COMMENT]
    );
  }

  #[Test]
  public function noCommentString() {
    $details= $this->parseComment('
      /**
       * @see   php://comment
       */
    ');
    Assert::equals('', $details[DETAIL_COMMENT]);
  }
  
  #[Test]
  public function scalar_parameter() {
    $details= $this->parseComment('/** @param  string param1 */');
    Assert::equals('string', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function array_parameter() {
    $details= $this->parseComment('/** @param  string[] param1 */');
    Assert::equals('string[]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function object_parameter() {
    $details= $this->parseComment('/** @param  util.Date param1 */');
    Assert::equals('util.Date', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function defaultParameter() {
    $details= $this->parseComment('/** @param  int param1 default 1 */');
    Assert::equals('int', $details[DETAIL_ARGUMENTS][0]);
  }
  
  #[Test]
  public function map_parameter() {
    $details= $this->parseComment('/** @param  [:string] map */');
    Assert::equals('[:string]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function generic_parameter_with_two_components() {
    $details= $this->parseComment('/** @param  util.collection.HashTable<string, util.Traceable> map */');
    Assert::equals('util.collection.HashTable<string, util.Traceable>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function generic_parameter_with_one_component() {
    $details= $this->parseComment('/** @param  util.collections.Vector<lang.Value> param1 */');
    Assert::equals('util.collections.Vector<lang.Value>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function nested_generic_parameter() {
    $details= $this->parseComment('/** @param  util.collections.Vector<util.collections.Vector<?>> map */');
    Assert::equals('util.collections.Vector<util.collections.Vector<?>>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function function_parameter() {
    $details= $this->parseComment('/** @param  function(string): string param1 */');
    Assert::equals('function(string): string', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function function_accepting_function_parameter() {
    $details= $this->parseComment('/** @param  function(function(): void): string param1 */');
    Assert::equals('function(function(): void): string', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function function_returning_function_parameter() {
    $details= $this->parseComment('/** @param  function(): function(): void param1 */');
    Assert::equals('function(): function(): void', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function function_returning_generic() {
    $details= $this->parseComment('/** @param  function(): util.Filter<string> param1 */');
    Assert::equals('function(): util.Filter<string>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function function_returning_array() {
    $details= $this->parseComment('/** @param  function(): int[] param1 */');
    Assert::equals('function(): int[]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function braced_function_type() {
    $details= $this->parseComment('/** @param  (function(): int) param1 */');
    Assert::equals('(function(): int)', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function array_of_functions() {
    $details= $this->parseComment('/** @param  (function(): int)[] param1 */');
    Assert::equals('(function(): int)[]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function map_of_functions() {
    $details= $this->parseComment('/** @param  [:function(): int] param1 */');
    Assert::equals('[:function(): int]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function map_of_functions_with_braces() {
    $details= $this->parseComment('/** @param  [:(function(): int)] param1 */');
    Assert::equals('[:(function(): int)]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[Test]
  public function throwsList() {
    $details= $this->parseComment('
      /**
       * Test method
       *
       * @throws  lang.IllegalArgumentException
       * @throws  lang.IllegalAccessException
       */
    ');
    Assert::equals('lang.IllegalArgumentException', $details[DETAIL_THROWS][0]);
    Assert::equals('lang.IllegalAccessException', $details[DETAIL_THROWS][1]);
  }
 
  #[Test]
  public function int_return_type() {
    $details= $this->parseComment('/** @return int */');
    Assert::equals('int', $details[DETAIL_RETURNS]);
  }

  #[Test]
  public function withClosure() {
    $details= (new ClassParser())->parseDetails('<?php
      class WithClosure_1 {

        /**
         * Creates a new answer
         *
         * @return  php.Closure
         */
        public function newAnswer() {
          return function() { return 42; };
        }
      }
    ?>');
    Assert::equals('Creates a new answer', $details[1]['newAnswer'][DETAIL_COMMENT]);
  }

  #[Test]
  public function withClosures() {
    $details= (new ClassParser())->parseDetails('<?php
      class WithClosure_2 {

        /**
         * Creates a new answer
         *
         * @return  php.Closure
         */
        public function newAnswer() {
          return function() { return 42; };
        }

        /**
         * Creates a new question
         *
         * @return  php.Closure
         */
        public function newQuestion() {
          return function() { return null; };   /* TODO: Remember question */
        }
      }
    ?>');
    Assert::equals('Creates a new question', $details[1]['newQuestion'][DETAIL_COMMENT]);
  }

  #[Test]
  public function use_statements_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use lang\unittest\Name;

      #[Value(new Name("test"))]
      class Test {
      }
    ');
    Assert::instance(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[Test]
  public function use_statements_with_alias_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use lang\unittest\Name as Named;

      #[Value(new Named("test"))]
      class Test {
      }
    ');
    Assert::instance(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[Test]
  public function grouped_use_statements_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use lang\unittest\{Name, DemoTest};

      #[Value(new Name("test"))]
      class Test extends DemoTest {
      }
    ');
    Assert::instance(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[Test]
  public function grouped_use_statements_with_alias_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use lang\unittest\{Name as Base};

      #[Value(new Base("test"))]
      class Test {
      }
    ');
    Assert::instance(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[Test]
  public function global_use_statement_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use ArrayObject;

      #[Value(new ArrayObject([]))]
      class Test {
      }
    ');
    Assert::instance(\ArrayObject::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[Test]
  public function multiple_global_use_statements_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use Traversable, ArrayObject;

      #[Value(new ArrayObject([]))]
      class Test {
      }
    ');
    Assert::instance(\ArrayObject::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[Test]
  public function php8_attributes_converted_to_xp_annotations() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Value("test")]
      class Test {
      }
    ');
    Assert::equals(['value' => 'test'], $actual['class'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function php8_attributes_with_trailing_comma() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Test, Value("test"),]
      class Test {
      }
    ');
    Assert::equals(['test' => null, 'value' => 'test'], $actual['class'][DETAIL_ANNOTATIONS]);
  }

  #[Test, Values(['\lang\unittest\Name', 'unittest\Name', 'Name'])]
  public function php8_attributes_with_named_arguments($name) {
    $actual= (new ClassParser())->parseDetails('<?php namespace lang;
      use lang\unittest\Name;

      #[Expect(class: '.$name.'::class)]
      class Test {
      }
    ');
    Assert::equals(['expect' => ['class' => Name::class]], $actual['class'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function php8_attributes_with_eval_argument() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Value(eval: "function() { return \"test\"; }")]
      class Test {
      }
    ');
    Assert::equals('test', $actual['class'][DETAIL_ANNOTATIONS]['value']());
  }

  #[Test]
  public function php8_attributes_with_array_eval_argument() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Value(eval: ["function() { return \"test\"; }"])]
      class Test {
      }
    ');
    Assert::equals('test', $actual['class'][DETAIL_ANNOTATIONS]['value']());
  }

  #[Test]
  public function php8_attributes_with_named_array_eval_argument() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Value(eval: ["func" => "function() { return \"test\"; }"])]
      class Test {
      }
    ');
    Assert::equals('test', $actual['class'][DETAIL_ANNOTATIONS]['value']['func']());
  }

  #[Test]
  public function php8_attributes_with_multiple_named_array_eval_arguments() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Value(eval: ["one" => "1", "two" => "2"])]
      class Test {
      }
    ');
    Assert::equals(['one' => 1, 'two' => 2], $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[Test]
  public function absolute_compound_php8_attributes() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[\unittest\annotations\Test]
      class Test {
      }
    ');
    Assert::equals(['test' => null], $actual['class'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function relative_compound_php8_attributes() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[unittest\annotations\Test]
      class Test {
      }
    ');
    Assert::equals(['test' => null], $actual['class'][DETAIL_ANNOTATIONS]);
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Unexpected ","/')]
  public function php8_attributes_cannot_have_multiple_arguments() {
    (new ClassParser())->parseDetails('<?php
      #[Value(1, 2)]
      class Test {
      }
    ');
  }

  #[Test]
  public function php8_type_attributes_original_names() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Value("test")]
      class Test {
      }
    ');
    Assert::equals(['value' => 'Value'], $actual['class'][DETAIL_TARGET_ANNO]);
  }

  #[Test]
  public function php8_method_attributes_original_names() {
    $actual= (new ClassParser())->parseDetails('<?php
      class Test {

        #[Value("test")]
        public function fixture() { }
      }
    ');
    Assert::equals(['value' => 'Value'], $actual[1]['fixture'][DETAIL_TARGET_ANNO]);
  }

  #[Test]
  public function php8_field_attributes_original_names() {
    $actual= (new ClassParser())->parseDetails('<?php
      class Test {

        #[Value("test")]
        public $fixture;
      }
    ');
    Assert::equals(['value' => 'Value'], $actual[0]['fixture'][DETAIL_TARGET_ANNO]);
  }

  #[Test]
  public function php8_param_attributes_original_names() {
    $actual= (new ClassParser())->parseDetails('<?php
      class Test {

        public function fixture(
          #[Value("test")]
          $p
        ) { }
      }
    ');
    Assert::equals(['$p' => ['value' => 'test'], 'value' => 'Value'], $actual[1]['fixture'][DETAIL_TARGET_ANNO]);
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Unexpected "," in eval/')]
  public function array_eval_cannot_have_multiple_arguments() {
    (new ClassParser())->parseDetails('<?php
      #[Value(eval: ["1", "2"])]
      class Test {
      }
    ');
  }

  #[Test]
  public function closure_use_not_evaluated() {
    (new ClassParser())->parseDetails('<?php 
      class Test {
        public function run() {
          $closure= function($a) use($b) { };
        }
      }
    ');
  }

  #[Test]
  public function short_array_syntax_in_arrays_of_arrays() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[Values([[1, 2], [3, 4]])]
      class Test {
      }
    ');
    Assert::equals([[1, 2], [3, 4]], $actual['class'][DETAIL_ANNOTATIONS]['values']);
  }

  #[Test]
  public function field_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[Test]
        public $fixture;
      }
    ');
    Assert::equals(['test' => null], $details[0]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[Test]
        public function fixture() { }
      }
    ');
    Assert::equals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function abstract_method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      abstract class Test {
        #[Test]
        public abstract function fixture();
      }
    ');
    Assert::equals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function interface_method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      interface Test {
        #[Test]
        public function fixture();
      }
    ');
    Assert::equals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function parser_not_confused_by_closure() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[Test]
        public function fixture() {
          return function() { };
        }
      }
    ');
    Assert::equals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function field_with_annotation_after_methods() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[Test]
        public function fixture() { }

        #[Test]
        public $fixture;
      }
    ');
    Assert::equals(['test' => null], $details[0]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test, Values(['\lang\unittest\Name', 'unittest\Name', 'Name'])]
  public function annotation_with_reference_to($parent) {
    $details= (new ClassParser())->parseDetails('<?php namespace lang;
      use lang\unittest\Name;

      class Test extends '.$parent.' {
        #[Fixture(new parent("Test"))]
        public function fixture() { }
      }'
    );
    Assert::equals(['fixture' => new Name('Test')], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test, Expect(class: ClassFormatException::class, message: '/Class does not have a parent/')]
  public function annotation_with_parent_reference_in_parentless_class() {
    (new ClassParser())->parseDetails('<?php
      class Test {
        #[Fixture(new parent())]
        public function fixture() { }
      }'
    );
  }

  #[Test]
  public function field_initializer_with_class_keyword() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {

        /** Property */
        private $classes= [self::class, parent::class];
      }
    ');
    Assert::equals(
      [DETAIL_COMMENT => '', DETAIL_ANNOTATIONS => [], DETAIL_TARGET_ANNO  => []],
      $details['class']
    );
  }

  #[Test, Values([['null', null], ['false', false], ['true', true], ['0', 0], ['1', 1], ['-1', -1], ['0x0', 0], ['0x2a', 42], ['00', 0], ['0644', 420], ['0.0', 0.0], ['1.5', 1.5], ['-1.5', -1.5], ['"hello"', 'hello'], ['""', ''], ['[1, 2, 3]', [1, 2, 3]], ['[]', []], ['["key" => "value"]', ['key' => 'value']],])]
  public function annotation_values($literal, $value) {
    $details= (new ClassParser())->parseDetails('<?php
      abstract class Test {
        #[Test('.$literal.')]
        public abstract function fixture();
      }
    ');
    Assert::equals(['test' => $value], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test, Values(['function() { return "Test"; }', 'fn() => "Test"',])]
  public function closures_inside_annotations($declaration) {
    $details= (new ClassParser())->parseDetails('<?php
      abstract class Test {
        #[Call('.$declaration.')]
        public abstract function fixture();
      }
    ');
    Assert::equals('Test', $details[1]['fixture'][DETAIL_ANNOTATIONS]['call']());
  }

  #[Test]
  public function fn_braced_expression() {
    $details= (new ClassParser())->parseDetails('<?php
      abstract class Test {
        #[Call(fn() => ord(chr(42)))]
        public abstract function fixture();
      }
    ');
    Assert::equals(42, $details[1]['fixture'][DETAIL_ANNOTATIONS]['call']());
  }

  #[Test, Values(['[fn() => [1, 2, 3]]', '[fn() => [1, 2, 3], ]', '[fn() => [1, 2, 3], 1]', '[fn() => [[1, [2][0], 3]][0]]',])]
  public function fn_with_arrays($declaration) {
    $details= (new ClassParser())->parseDetails('<?php
      abstract class Test {
        #[Call('.$declaration.')]
        public abstract function fixture();
      }
    ');
    Assert::equals([1, 2, 3], $details[1]['fixture'][DETAIL_ANNOTATIONS]['call'][0]());
  }

  #[Test]
  public function anonymous_class() {
    $details= (new ClassParser())->parseDetails('<?php
      new class() { }
    ');
    Assert::equals(
      [DETAIL_COMMENT => null, DETAIL_ANNOTATIONS => [], DETAIL_ARGUMENTS => null],
      $details['class']
    );
  }

  #[Test]
  public function anonymous_class_with_arguments() {
    $details= (new ClassParser())->parseDetails('<?php
      new class(1, 2, 3) { }
    ');
    Assert::equals(
      [DETAIL_COMMENT => null, DETAIL_ANNOTATIONS => [], DETAIL_ARGUMENTS => null],
      $details['class']
    );
  }

  #[Test]
  public function anonymous_class_member() {
    $details= (new ClassParser())->parseDetails('<?php
      new class() {
        #[Test]
        public $fixture;
      }
    ');
    Assert::equals(['test' => null], $details[0]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function anonymous_class_method() {
    $details= (new ClassParser())->parseDetails('<?php
      new class() {
        #[Test]
        public function fixture() { }
      }
    ');
    Assert::equals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[Test]
  public function new_after_curly_open_in_string() {
    $details= (new ClassParser())->parseDetails('<?php
      /** Comment */
      class Test {
        private $name;

        public function run() {
          $name= "{$this->name}.txt";
          new File($name);
        }
      }
    ');
    Assert::equals(
      [DETAIL_COMMENT => 'Comment', DETAIL_ANNOTATIONS => [], DETAIL_TARGET_ANNO  => []],
      $details['class']
    );
  }

  #[Test]
  public function annotation_with_curly_open_in_string() {
    $details= (new ClassParser())->parseDetails('<?php
      #[Run(function($name) { return "{$name}.txt"; })]
      class Test {
      }
    ');
    Assert::equals('test.txt', $details['class'][DETAIL_ANNOTATIONS]['run']('test'));
  }
}