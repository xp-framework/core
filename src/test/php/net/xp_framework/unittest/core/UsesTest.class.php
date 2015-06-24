<?php namespace net\xp_framework\unittest\core;

use lang\Runtime;
use lang\Process;
use unittest\PrerequisitesNotMetError;

/**
 * TestCase for `uses()` statement
 *
 * @deprecated Use the `use` statement and namespaces instead
 */
class UsesTest extends \unittest\TestCase {

  /**
   * Skips tests if process execution has been disabled.
   *
   * @return void
   */
  #[@beforeClass]
  public static function verifyProcessExecutionEnabled() {
    if (Process::$DISABLED) {
      throw new PrerequisitesNotMetError('Process execution disabled', NULL, array('enabled'));
    }
  }

  /**
   * Runs given code in a new runtime
   *
   * @param   string code
   * @return  var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function run($code) {
    with ($out= $err= '', $p= Runtime::getInstance()->newInstance(NULL, 'class', 'xp.runtime.Evaluate', [])); {
      $p->in->write($code);
      $p->in->close();

      // Read output
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Close child process
      $exitv= $p->close();
    }
    return array($exitv, explode("\n", rtrim($out)), explode("\n", rtrim($err)));
  }

  /**
   * Issues a uses() command inside a new runtime for every class given
   * and returns a line indicating success or failure for each of them.
   *
   * @param   string[] uses
   * @param   string decl
   * @return  var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function useAllOf($uses, $decl= '') {
    return $this->run($decl.'
      ClassLoader::registerPath(\''.strtr($this->getClass()->getClassLoader()->path, '\\', '/').'\');
      $errors= 0;
      foreach (array("'.implode('", "', $uses).'") as $class) {
        try {
          uses($class);
          echo "+OK ", $class, "\n";
        } catch (Throwable $e) {
          echo "-ERR ", $class, ": ", nameof($e), "\n";
          $errors++;
        }
      }
      exit($errors);
    ');
  }

  /**
   * Assertion helper
   *
   * @param  int exitv expected exit value
   * @param  string[] out expected STDOUT lines
   * @param  string[] err expected STDERR lines
   * @param  var[] r actualy useAllOf() output
   */
  protected function assertResult($exitv, $out, $err, $r) {
    $this->assertEquals(
      array('exitv' => $exitv, 'out' => $out, 'err' => $err),
      array('exitv' => $r[0], 'out' => $r[1], 'err' => $r[2])
    );
  }

  #[@test]
  public function useExistingClass() {
    $this->assertResult(
      0, 
      array('+OK '.nameof($this)),
      array(''),
      $this->useAllOf(array(nameof($this)))
    );
  }

  #[@test]
  public function useNonExistantClass() {
    $this->assertResult(
      1, 
      array('-ERR does.not.exist: lang.ClassNotFoundException'),
      array(''),
      $this->useAllOf(array('does.not.exist'))
    );
  }

  #[@test]
  public function useClasses() {
    $this->assertResult(
      1,
      array('+OK '.nameof($this), '-ERR does.not.exist: lang.ClassNotFoundException'),
      array(''),
      $this->useAllOf(array(nameof($this), 'does.not.exist'))
    );
  }

  /**
   * Test using a class that has a circular dependency
   *
   * A.class.php
   * <code>
   *   uses('B');
   *
   *   class A extends Object { }
   * </code>
   *
   * B.class.php
   * <code>
   *   uses('C');
   *
   *   class B extends Object { }
   * </code>
   *
   * C.class.php
   * <code>
   *   uses('A');
   *
   *   class C extends Object { }
   * </code>
   *
   */
  #[@test]
  public function circularDependency() {
    $this->assertResult(
      0, 
      array('+OK net.xp_framework.unittest.bootstrap.A'),
      array(''),
      $this->useAllOf(array('net.xp_framework.unittest.bootstrap.A'))
    );
  }

  /**
   * Test using a class that has a circular dependency when
   * ticks are set to 1
   *
   * @see   http://bugs.xp-framework.net/show_bug.cgi?id=19
   */
  #[@test]
  public function circularDependencyWithTicks() {
    $this->assertResult(
      0, 
      array('+OK net.xp_framework.unittest.bootstrap.A'),
      array(''),
      $this->useAllOf(array('net.xp_framework.unittest.bootstrap.A'), 'declare(ticks=1)')
    );
  }

  #[@test]
  public function uses_makes_classes_accessible_by_their_long_names() {
    $this->assertResult(
      0,
      ['bool(true)'],
      [''],
      $this->run('uses("lang.reflect.Proxy"); var_dump(class_exists("lang\\\\reflect\\\\Proxy", false));')
    );
  }

  #[@test]
  public function uses_makes_interfaces_accessible_by_their_long_names() {
    $this->assertResult(
      0,
      ['bool(true)'],
      [''],
      $this->run('uses("lang.reflect.InvocationHandler"); var_dump(interface_exists("lang\\\\reflect\\\\InvocationHandler", false));')
    );
  }

  #[@test]
  public function uses_makes_classes_accessible_by_their_short_names() {
    $this->assertResult(
      0,
      ['bool(true)'],
      [''],
      $this->run('uses("lang.reflect.Proxy"); var_dump(class_exists("Proxy", false));')
    );
  }

  #[@test]
  public function uses_makes_interfaces_accessible_by_their_short_names() {
    $this->assertResult(
      0,
      ['bool(true)'],
      [''],
      $this->run('uses("lang.reflect.InvocationHandler"); var_dump(interface_exists("InvocationHandler", false));')
    );
  }

  #[@test, @values(['lang.reflect.Proxy', 'net.xp_framework.unittest.bootstrap.A'])]
  public function uses_same_class_twice_does_not_create_problem($class) {
    $this->assertResult(
      0,
      ['array(0) {', '}'],
      [''],
      $this->run('xp::gc(); uses("'.$class.'", "'.$class.'"); var_dump(xp::$errors);')
    );
  }

  #[@test]
  public function uses_same_interface_twice_does_not_create_problem() {
    $this->assertResult(
      0,
      ['array(0) {', '}'],
      [''],
      $this->run('xp::gc(); uses("lang.reflect.InvocationHandler", "lang.reflect.InvocationHandler"); var_dump(xp::$errors);')
    );
  }

  #[@test]
  public function uses_class_with_import_Function() {
    $this->assertResult(
      0,
      array('+OK lang.ResourceProvider'),
      array(''),
      $this->useAllOf(array('lang.ResourceProvider'))
    );
  }
}
