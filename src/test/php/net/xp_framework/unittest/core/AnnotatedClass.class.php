<?php namespace net\xp_framework\unittest\core;

use unittest\{Ignore, Limit, Test};

class AnnotatedClass {

  /**
   * Method annotated with one simple annotation
   *
   */
  #[Simple]
  public function simple() { }

  /**
   * Method annotated with more than one annotation
   *
   */
  #[One, Two, Three]
  public function multiple() { }

  /**
   * Method annotated with an annotation with a string value
   *
   */
  #[Strval('String value')]
  public function stringValue() { }

  /**
   * Method annotated with an annotation with a one key/value pair
   *
   */
  #[Config(key: 'value')]
  public function keyValuePair() { }

  /**
   * Method annotated with an annotation with a hash value containing one
   * key/value pair
   *
   */
  #[Config(['key' => 'value'])]
  public function hashValue() { }

  /**
   * Unittest method annotated with @test, @ignore and @limit
   *
   */
  #[Test, Ignore, Limit(['time' => 0.1, 'memory' => 100])]
  public function testMethod() { }

}