<?php namespace net\xp_framework\unittest\reflection;

use lang\XPClass;
use lang\Object;
use lang\Type;
use lang\MapType;
use lang\Primitive;
use lang\ElementNotFoundException;
use lang\IllegalArgumentException;
use lang\IllegalAccessException;
use lang\reflect\Field;
use util\Date;

class FieldsTest extends \unittest\TestCase {
  private $fixture;

  /**
   * Sets up test case
   *
   * @return void
   */
  public function setUp() {
    $this->fixture= XPClass::forName('net.xp_framework.unittest.reflection.TestClass');
  }

  /**
   * Assertion helper
   *
   * @param  lang.Generic $var
   * @param  lang.Generic[] $list
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertNotContained($var, $list) {
    foreach ($list as $i => $element) {
      if ($element->equals($var)) $this->fail('Element contained', 'Found at offset '.$i, null);
    }
  }

  /**
   * Assertion helper
   *
   * @param  lang.Generic $var
   * @param  lang.Generic[] $list
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertContained($var, $list) {
    foreach ($list as $i => $element) {
      if ($element->equals($var)) return;
    }
    $this->fail('Element not contained in list', null, $var);
  }

  /**
   * Helper method
   *
   * @param  int $modifiers
   * @param  string $field
   * @return void
   * @throws unittest.AssertionFailedError
   */
  protected function assertModifiers($modifiers, $field) {
    $this->assertEquals($modifiers, $this->fixture->getField($field)->getModifiers());
  }

  #[@test]
  public function fields() {
    $fields= $this->fixture->getFields();
    $this->assertInstanceOf('lang.reflect.Field[]', $fields);
    $this->assertContained($this->fixture->getField('inherited'), $fields);
  }

  #[@test]
  public function declaredFields() {
    $fields= $this->fixture->getDeclaredFields();
    $this->assertInstanceOf('lang.reflect.Field[]', $fields);
    $this->assertNotContained($this->fixture->getField('inherited'), $fields);
  }

  #[@test]
  public function declaredField() {
    $this->assertEquals(
      $this->fixture,
      $this->fixture->getField('map')->getDeclaringClass()
    );
  }

  #[@test]
  public function inheritedField() {
    $this->assertEquals(
      $this->fixture->getParentClass(),
      $this->fixture->getField('inherited')->getDeclaringClass()
    );
  }

  #[@test]
  public function nonExistantField() {
    $this->assertFalse($this->fixture->hasField('@@nonexistant@@'));
  }
  
  #[@test, @expect(ElementNotFoundException::class)]
  public function getNonExistantField() {
    $this->fixture->getField('@@nonexistant@@');
  }

  #[@test]
  public function checkSpecialIdField() {
    $this->assertFalse($this->fixture->hasField('__id'));
  }
  
  #[@test, @expect(ElementNotFoundException::class)]
  public function getSpecialIdField() {
    $this->fixture->getField('__id');
  }

  #[@test]
  public function publicField() {
    $this->assertModifiers(MODIFIER_PUBLIC, 'date');
  }

  #[@test]
  public function protectedField() {
    $this->assertModifiers(MODIFIER_PROTECTED, 'size');
  }

  #[@test]
  public function privateField() {
    $this->assertModifiers(MODIFIER_PRIVATE, 'factor');
  }

  #[@test]
  public function publicStaticField() {
    $this->assertModifiers(MODIFIER_PUBLIC | MODIFIER_STATIC, 'initializerCalled');
  }

  #[@test]
  public function privateStaticField() {
    $this->assertModifiers(MODIFIER_PRIVATE | MODIFIER_STATIC, 'cache');
  }

  #[@test]
  public function dateField() {
    $this->assertTrue($this->fixture->hasField('date'));
    with ($field= $this->fixture->getField('date')); {
      $this->assertInstanceOf(Field::class, $field);
      $this->assertEquals('date', $field->getName());
      $this->assertEquals(XPClass::forName(Date::class), $field->getType());
      $this->assertTrue($this->fixture->equals($field->getDeclaringClass()));
    }
  }

  #[@test]
  public function dateFieldValue() {
    $this->assertInstanceOf(Date::class, $this->fixture->getField('date')->get($this->fixture->newInstance()));
  }

  #[@test]
  public function dateFieldRoundTrip() {
    $instance= $this->fixture->newInstance();
    $date= Date::now();
    $field= $this->fixture->getField('date');
    $field->set($instance, $date);
    $this->assertEquals($date, $field->get($instance));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function getDateFieldValueOnWrongObject() {
    $this->fixture->getField('date')->get(new Object());
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function setDateFieldValueOnWrongObject() {
    $this->fixture->getField('date')->set(new Object(), Date::now());
  }

  #[@test]
  public function initializerCalledFieldValue() {
    $this->assertEquals(true, $this->fixture->getField('initializerCalled')->get(null));
  }

  #[@test]
  public function initializerCalledFieldRoundTrip() {
    with ($f= $this->fixture->getField('initializerCalled')); {
      $f->set(null, false);
      $this->assertEquals(false, $f->get(null));
      $f->set(null, true);
      $this->assertEquals(true, $f->get(null));
    }
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function getCacheFieldValue() {
    $this->fixture->getField('cache')->get(null);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function setCacheFieldValue() {
    $this->fixture->getField('cache')->set(null, []);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function getSizeFieldValue() {
    $this->fixture->getField('size')->get($this->fixture->newInstance());
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function setSizeFieldValue() {
    $this->fixture->getField('size')->set($this->fixture->newInstance(), 1);
  }

  #[@test, @expect(IllegalAccessException::class)]
  public function factorFieldValue() {
    $this->fixture->getField('factor')->get($this->fixture->newInstance());
  }

  #[@test]
  public function dateFieldString() {
    $this->assertEquals(
      'public util.Date net.xp_framework.unittest.reflection.TestClass::$date', 
      $this->fixture->getField('date')->toString()
    );
  }

  #[@test]
  public function cacheFieldString() {
    $this->assertEquals(
      'private static var net.xp_framework.unittest.reflection.TestClass::$cache', 
      $this->fixture->getField('cache')->toString()
    );
  }

  #[@test]
  public function dateFieldType() {
    $this->assertEquals(XPClass::forName(Date::class), $this->fixture->getField('date')->getType());
  }

  #[@test]
  public function dateFieldTypeName() {
    $this->assertEquals('util.Date', $this->fixture->getField('date')->getTypeName());
  }

  #[@test]
  public function mapFieldType() {
    $this->assertEquals(MapType::forName('[:lang.Object]'), $this->fixture->getField('map')->getType());
  }

  #[@test]
  public function mapFieldTypeName() {
    $this->assertEquals('[:lang.Object]', $this->fixture->getField('map')->getTypeName());
  }

  #[@test]
  public function sizeFieldType() {
    $this->assertEquals(Primitive::$INT, $this->fixture->getField('size')->getType());
  }

  #[@test]
  public function sizeFieldTypeName() {
    $this->assertEquals('int', $this->fixture->getField('size')->getTypeName());
  }

  #[@test]
  public function cacheFieldType() {
    $this->assertEquals(Type::$VAR, $this->fixture->getField('cache')->getType());
  }

  #[@test]
  public function cacheFieldTypeName() {
    $this->assertEquals('var', $this->fixture->getField('cache')->getTypeName());
  }

  #[@test]
  public function fieldTypeForInheritedField() {
    $this->assertEquals(XPClass::forName('lang.Object'), $this->fixture->getField('inherited')->getType());
  }

  #[@test]
  public function fieldTypeNameForInheritedField() {
    $this->assertEquals('lang.Object', $this->fixture->getField('inherited')->getTypeName());
  }
}
