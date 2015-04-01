<?php namespace net\xp_framework\unittest\reflection;

use unittest\TestCase;
use lang\reflect\ClassParser;

define('APIDOC_TAG',        0x0001);
define('APIDOC_VALUE',      0x0002);

/**
 * Tests the class details gathering internals
 *
 * @see  xp://lang.XPClass#detailsForClass
 * @see  https://github.com/xp-framework/xp-framework/issues/230
 * @see  https://github.com/xp-framework/xp-framework/issues/270
 */
class ClassDetailsTest extends TestCase {

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
        class Test extends Object {
          '.$comment.'
          public function test($param1) { }
        }
      ?>',
      $this->name
    );
    return $details[1]['test'];
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
    $details= $this->parseComment('/** @param  util.collections.Vector<lang.Object> param1 */');
    $this->assertEquals('util.collections.Vector<lang.Object>', $details[DETAIL_ARGUMENTS][0]);
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

  /** @return [:var] */
  protected function dummyDetails() {
    return (new ClassParser())->parseDetails('<?php
      class DummyDetails extends Object {
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
  public function canBeCachedViaXpRegistry() {
    with (\xp::$registry['details.'.($fixture= 'DummyDetails')]= $details= $this->dummyDetails()); {
      $actual= \lang\XPClass::detailsForClass($fixture);
      unset(\xp::$registry['details.'.$fixture]);
    }
    $this->assertEquals($details, $actual);
  }

  #[@test]
  public function use_statements_evaluated() {
    $actual= (new ClassParser())->parseDetails('<?php namespace test;
      use lang\\Object;

      #[@value(new Object())]
      class Test extends Object {
      }
    ');
    $this->assertInstanceOf('lang.Object', $actual['class'][DETAIL_ANNOTATIONS]['value']);
  }

  #[@test]
  public function closure_use_not_evaluated() {
    (new ClassParser())->parseDetails('<?php 
      class Test extends Object {
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
      class Test extends Object {
      }
    ');
    $this->assertEquals([[1, 2], [3, 4]], $actual['class'][DETAIL_ANNOTATIONS]['values']);
  }

  #[@test]
  public function field_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test extends Object {
        #[@test]
        public $fixture;
      }
    ');
    $this->assertEquals(['test' => null], $details[0]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test]
  public function method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      class Test extends Object {
        #[@test]
        public function fixture() { }
      }
    ');
    $this->assertEquals(['test' => null], $details[1]['fixture'][DETAIL_ANNOTATIONS]);
  }

  #[@test]
  public function abstract_method_annotations() {
    $details= (new ClassParser())->parseDetails('<?php
      abstract class Test extends Object {
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
      class Test extends Object {
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
      class Test extends Object {
        #[@test]
        public function fixture() { }

        #[@test]
        public $fixture;
      }
    ');
    $this->assertEquals(['test' => null], $details[0]['fixture'][DETAIL_ANNOTATIONS]);
  }
}
