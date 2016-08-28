<?php namespace net\xp_framework\unittest\util;

use util\Observable;
use util\Observer;
use lang\ClassLoader;

/**
 * Test Observable class
 *
 * @see  xp://util.Observable
 */
class ObservableTest extends \unittest\TestCase {
  protected static $observable;

  #[@beforeClass]
  public static function defineObservable() {
    self::$observable= ClassLoader::defineClass('net.xp_framework.unittest.util.ObservableFixture', Observable::class, [], '{
      private $value= 0;

      public function setValue($value) {
        $this->value= $value;
        $this->setChanged();
        $this->notifyObservers();
      }

      public function getValue() {
        return $this->value;
      }
    }');
  }

  #[@test]
  public function originally_unchanged() {
    $o= self::$observable->newInstance();
    $this->assertFalse($o->hasChanged());
  }

  #[@test]
  public function changed() {
    $o= self::$observable->newInstance();
    $o->setChanged();
    $this->assertTrue($o->hasChanged());
  }

  #[@test]
  public function change_cleared() {
    $o= self::$observable->newInstance();
    $o->setChanged();
    $o->clearChanged();
    $this->assertFalse($o->hasChanged());
  }

  #[@test]
  public function add_observer_returns_added_observer() {
    $observer= new class() implements Observer {
      public function update($obs, $arg= null) {
        /* Intentionally empty */
      }
    };
    $o= self::$observable->newInstance();
    $this->assertEquals($observer, $o->addObserver($observer));
  }

  #[@test]
  public function observer_gets_called_with_observable() {
    $observer= new class() implements Observer {
      public $calls = [];
      public function update($obs, $arg= null) {
        $this->calls[]= [$obs, $arg];
      }
    };
    $o= self::$observable->newInstance();
    $o->addObserver($observer);
    $o->setValue(5);
    $this->assertEquals([[$o, null]], $observer->calls);
  }
}
