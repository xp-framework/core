<?php namespace net\xp_framework\unittest\reflection;

use lang\reflect\ClassParser;
use lang\ClassFormatException;
use net\xp_framework\unittest\Name;

define('APIDOC_TAG',        0x0001);
define('APIDOC_VALUE',      0x0002);

/**
 * Tests the class details gathering internals
 *
 * @see  xp://lang.XPClass#detailsForClass
 * @see  https://github.com/xp-framework/xp-framework/issues/230
 * @see  https://github.com/xp-framework/xp-framework/issues/270
 */
class ClassDetailsTest extends \unittest\TestCase {

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
      $this->name
    );
    return $details[1]['test'];
  }
  
  #[@test, @values(['class', 'interface', 'trait'])]
  public function parses($kind) {
    $details= (new ClassParser())->parseDetails('<?php '.$kind.' Test { }');
    $this->assertEquals(
      [DETAIL_COMMENT => '', DETAIL_ANNOTATIONS => [], DETAIL_ARGUMENTS => 'Test'],
      $details['class']
    );
  }

  #[@test]
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
    $this->assertEquals(
      "A protected method\n\nNote: Not compatible with PHP 4.1.2!",
      $details[DETAIL_COMMENT]
    );
  }

  #[@test]
  public function noCommentString() {
    $details= $this->parseComment('
      /**
       * @see   php://comment
       */
    ');
    $this->assertEquals('', $details[DETAIL_COMMENT]);
  }
  
  #[@test]
  public function scalar_parameter() {
    $details= $this->parseComment('/** @param  string param1 */');
    $this->assertEquals('string', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function array_parameter() {
    $details= $this->parseComment('/** @param  string[] param1 */');
    $this->assertEquals('string[]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function object_parameter() {
    $details= $this->parseComment('/** @param  util.Date param1 */');
    $this->assertEquals('util.Date', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function defaultParameter() {
    $details= $this->parseComment('/** @param  int param1 default 1 */');
    $this->assertEquals('int', $details[DETAIL_ARGUMENTS][0]);
  }
  
  #[@test]
  public function map_parameter() {
    $details= $this->parseComment('/** @param  [:string] map */');
    $this->assertEquals('[:string]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function generic_parameter_with_two_components() {
    $details= $this->parseComment('/** @param  util.collection.HashTable<string, util.Traceable> map */');
    $this->assertEquals('util.collection.HashTable<string, util.Traceable>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function generic_parameter_with_one_component() {
    $details= $this->parseComment('/** @param  util.collections.Vector<unittest.TestCase> param1 */');
    $this->assertEquals('util.collections.Vector<unittest.TestCase>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function nested_generic_parameter() {
    $details= $this->parseComment('/** @param  util.collections.Vector<util.collections.Vector<?>> map */');
    $this->assertEquals('util.collections.Vector<util.collections.Vector<?>>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function function_parameter() {
    $details= $this->parseComment('/** @param  function(string): string param1 */');
    $this->assertEquals('function(string): string', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function function_accepting_function_parameter() {
    $details= $this->parseComment('/** @param  function(function(): void): string param1 */');
    $this->assertEquals('function(function(): void): string', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function function_returning_function_parameter() {
    $details= $this->parseComment('/** @param  function(): function(): void param1 */');
    $this->assertEquals('function(): function(): void', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function function_returning_generic() {
    $details= $this->parseComment('/** @param  function(): util.Filter<string> param1 */');
    $this->assertEquals('function(): util.Filter<string>', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function function_returning_array() {
    $details= $this->parseComment('/** @param  function(): int[] param1 */');
    $this->assertEquals('function(): int[]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function array_of_functions() {
    $details= $this->parseComment('/** @param  (function(): int)[] param1 */');
    $this->assertEquals('(function(): int)[]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function map_of_functions() {
    $details= $this->parseComment('/** @param  [:function(): int] param1 */');
    $this->assertEquals('[:function(): int]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function map_of_functions_with_braces() {
    $details= $this->parseComment('/** @param  [:(function(): int)] param1 */');
    $this->assertEquals('[:(function(): int)]', $details[DETAIL_ARGUMENTS][0]);
  }

  #[@test]
  public function throwsList() {
    $details= $this->parseComment('
      /**
       * Test method
       *
       * @throws  lang.IllegalArgumentException
       * @throws  lang.IllegalAccessException
       */
    ');
    $this->assertEquals('lang.IllegalArgumentException', $details[DETAIL_THROWS][0]);
    $this->assertEquals('lang.IllegalAccessException', $details[DETAIL_THROWS][1]);
  }
 
  #[@test]
  public function int_return_type() {
    $details= $this->parseComment('/** @return int */');
    $this->assertEquals('int', $details[DETAIL_RETURNS]);
  }

  #[@test]
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
    $this->assertEquals('Creates a new answer', $details[1]['newAnswer'][DETAIL_COMMENT]);
  }

  #[@test]
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
          return function() { return NULL; };   /* TODO: Remember question */
        }
      }
    ?>');
    $this->assertEquals('Creates a new question', $details[1]['newQuestion'][DETAIL_COMMENT]);
  }

  /** @return [:var] */
  protected function dummyDetails() {
    return (new ClassParser())->parseDetails('<?php
      class DummyDetails {
        protected $test = true;

        #[@test]
        public function test() { }
      }
    ?>');
  }

  #[@test]
  public function canBeCached() {
    with (\xp::$meta[$fixture= 'DummyDetails']= $details= $this->dummyDetails()); {
      $actual= \lang\XPClass::detailsForClass($fixture);
      unset(\xp::$meta[$fixture]);
    }
    $this->assertEquals($details, $actual);
  }

  #[@test]
  public function use_statements_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use net\xp_framework\unittest\Name;

      #[@value(new Name("test"))]
      class Test {
      }
    ');
    $this->assertInstanceOf(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[@test]
  public function use_statements_with_alias_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use net\xp_framework\unittest\Name as Value;

      #[@value(new Value("test"))]
      class Test {
      }
    ');
    $this->assertInstanceOf(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[@test]
  public function grouped_use_statements_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use net\xp_framework\unittest\{Name, IgnoredOnHHVM};

      #[@value(new Name("test"))]
      class Test {
      }
    ');
    $this->assertInstanceOf(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[@test]
  public function grouped_use_statements_with_alias_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use net\xp_framework\unittest\{Name as Base};

      #[@value(new Base("test"))]
      class Test {
      }
    ');
    $this->assertInstanceOf(Name::class, $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[@test]
  public function closure_use_not_evaluated() {
    (new ClassParser())->parseDetails('<?php 
      class Test {
        public function run() {
          $closure= function($a) use($b) { };
        }
      }
    ');
  }

  #[@test]
  public function short_array_syntax_in_arrays_of_arrays() {
    $actual= (new ClassParser())->parseDetails('<?php
      #[@values([
      #  [1, 2],
      #  [3, 4]
      #])]
      class Test {
      }
    ');
    $this->assertEquals([[1, 2], [3, 4]], $actual['class'][DETAIL_ANNOTATIONS]['values']);
  }

  #[@test]
  public function field_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[@test]
        public $fixture;
      }
    ');
    $this->assertEquals(['test' => null], $details[0]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test]
  public function method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[@test]
        public function fixture() { }
      }
    ');
    $this->assertEquals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test]
  public function abstract_method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      abstract class Test {
        #[@test]
        public abstract function fixture();
      }
    ');
    $this->assertEquals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test]
  public function interface_method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      interface Test {
        #[@test]
        public function fixture();
      }
    ');
    $this->assertEquals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test]
  public function parser_not_confused_by_closure() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[@test]
        public function fixture() {
          return function() { };
        }
      }
    ');
    $this->assertEquals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test]
  public function field_with_annotation_after_methods() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        #[@test]
        public function fixture() { }

        #[@test]
        public $fixture;
      }
    ');
    $this->assertEquals(['test' => null], $details[0]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test, @expect(class= ClassFormatException::class, withMessage= '/Class does not have a parent/')]
  public function annotation_with_parent_reference_in_parentless_class() {
    (new ClassParser())->parseDetails('<?php
      class Test {
        #[@fixture(new parent())]
        public function fixture() { }
      }',
      Name::class
    );
  }

  #[@test]
  public function field_initializer_with_class_keyword() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test {
        private $classes= [self::class, parent::class];
      }
    ');
    $this->assertEquals(
      [DETAIL_COMMENT => '', DETAIL_ANNOTATIONS => [], DETAIL_ARGUMENTS => 'Test'],
      $details['class']
    );
  }
}