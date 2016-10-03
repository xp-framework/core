<?php namespace net\xp_framework\unittest\core;

use lang\Runtime;

class FromTest extends \unittest\TestCase {

  /**
   * Runs sourcecode inside a new runtime
   *
   * @param   string $src
   * @return  var[] an array with three elements: exitcode, stdout and stderr contents
   */
  protected function runInNewRuntime($src) {
    return with (Runtime::getInstance()->newInstance(null, 'class', 'xp.runtime.Evaluate', []), function($p) use($src) {
      $p->in->write($src);
      $p->in->close();

      // Read output
      $out= $err= '';
      while ($b= $p->out->read()) { $out.= $b; }
      while ($b= $p->err->read()) { $err.= $b; }

      // Close child process
      $exitv= $p->close();
      return [$exitv, $out, $err];
    });
  }

  #[@test]
  public function type_from_xp_core() {
    $r= $this->runInNewRuntime('
      from("xp-framework/core", ["util\\Date"], "");

      echo typeof(new Date());
    ');
    $this->assertEquals([0, 'util.Date', ''], $r);
  }

  #[@test]
  public function types_from_xp_core() {
    $r= $this->runInNewRuntime('
      from("xp-framework/core", ["util\\Date", "util\\TimeZone"], "");

      echo typeof(new Date()), " & ", typeof(new TimeZone("Europe/Berlin"));
    ');
    $this->assertEquals([0, 'util.Date & util.TimeZone', ''], $r);
  }

  #[@test]
  public function grouped_use_declarations() {
    $r= $this->runInNewRuntime('
      from("xp-framework/core", ["util\\{Date, TimeZone}"], "");

      echo typeof(new Date()), " & ", typeof(new TimeZone("Europe/Berlin"));
    ');
    $this->assertEquals([0, 'util.Date & util.TimeZone', ''], $r);
  }

  #[@test]
  public function all_types_from_util_package() {
    $r= $this->runInNewRuntime('
      from("xp-framework/core", ["util\\*"], "");

      echo typeof(new Date()), " & ", typeof(new TimeZone("Europe/Berlin"));
    ');
    $this->assertEquals([0, 'util.Date & util.TimeZone', ''], $r);
  }

  #[@test]
  public function import_into_namespace() {
    $r= $this->runInNewRuntime('namespace test;
      from("xp-framework/core", ["util\\Date"], "test");

      echo typeof(new \test\Date());
    ');
    $this->assertEquals([0, 'util.Date', ''], $r);
  }

  #[@test]
  public function import_into_namespace_detects_namespace() {
    $r= $this->runInNewRuntime('namespace test;
      from("xp-framework/core", ["util\\Date"]);

      echo typeof(new \test\Date());
    ');
    $this->assertEquals([0, 'util.Date', ''], $r);
  }

  #[@test]
  public function all_types_from_util_package_doesnt_shadow_local_types() {
    $r= $this->runInNewRuntime('
      from("xp-framework/core", ["util\\*"], "");

      class TimeZone { }

      echo typeof(new Date()), " & ", typeof(new TimeZone());
    ');
    $this->assertEquals([0, 'util.Date & TimeZone', ''], $r);
  }

  #[@test]
  public function all_types_from_util_package_doesnt_shadow_namespace_types() {
    $r= $this->runInNewRuntime('namespace test;
      from("xp-framework/core", ["util\\*"], "test");

      class TimeZone { }

      echo typeof(new Date()), " & ", typeof(new TimeZone());
    ');
    $this->assertEquals([0, 'util.Date & test.TimeZone', ''], $r);
  }

  #[@test]
  public function non_existant_library() {
    $r= $this->runInNewRuntime('
      from("non-existant/library", ["util\\Date"], "");
    ');
    $this->assertEquals(255, $r[0]);
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], "Could not load module non-existant/library"),
      \xp::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test]
  public function non_existant_type() {
    $r= $this->runInNewRuntime('
      from("xp-framework/core", ["util\\NonExistantType"], "");
    ');
    $this->assertEquals(255, $r[0]);
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], "Cannot import util\NonExistantType"),
      \xp::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }

  #[@test]
  public function non_existant_type_in_grouped_use_declaration() {
    $r= $this->runInNewRuntime('
      from("xp-framework/core", ["util\{Date, NonExistantType}"], "");
    ');
    $this->assertEquals(255, $r[0]);
    $this->assertTrue(
      (bool)strstr($r[1].$r[2], "Cannot import util\NonExistantType"),
      \xp::stringOf(['out' => $r[1], 'err' => $r[2]])
    );
  }
}