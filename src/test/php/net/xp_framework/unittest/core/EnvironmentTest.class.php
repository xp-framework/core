<?php namespace net\xp_framework\unittest\core;

use lang\Environment;
use lang\IllegalArgumentException;
use lang\IllegalStateException;
use net\xp_framework\unittest\IgnoredOnHHVM;

#[@action(new IgnoredOnHHVM())]
class EnvironmentTest extends \unittest\TestCase {
  private static $set;

  #[@beforeClass]
  public static function clearXDG() {
    $remove= [];
    foreach ($_ENV as $variable => $value) {
      if (0 === strncmp('XDG_', $variable, 4)) $remove[$variable]= null;
    }
    self::$set= new EnvironmentSet($remove);
  }

  #[@afterClass]
  public static function restoreXDG() {
    self::$set->close();
  }

  #[@test]
  public function variable() {
    with (new EnvironmentSet(['HOME' => '/home/test']), function() {
      $this->assertEquals('/home/test', Environment::variable('HOME'));
    });
  }

  #[@test]
  public function variable_with_alternatives() {
    with (new EnvironmentSet(['USERPROFILE' => null, 'HOME' => '/home/test']), function() {
      $this->assertEquals('/home/test', Environment::variable(['USERPROFILE', 'HOME']));
    });
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function non_existant_variable() {
    with (new EnvironmentSet(['HOME' => null]), function() {
      Environment::variable('HOME');
    });
  }

  #[@test, @values(['/home/default', null])]
  public function default_used_for_non_existant_variable($default) {
    with (new EnvironmentSet(['HOME' => null]), function() use($default) {
      $this->assertEquals($default, Environment::variable('HOME', $default));
    });
  }

  #[@test]
  public function default_function_not_invoked_for_existant_variable() {
    with (new EnvironmentSet(['HOME' => '/home/test']), function() {
      $this->assertEquals('/home/test', Environment::variable('HOME', function() {
        throw new IllegalStateException('Never reached');
      }));
    });
  }

  #[@test]
  public function default_function_invoked_for_non_existant_variable() {
    with (new EnvironmentSet(['HOME' => null]), function() {
      $this->assertEquals('/home/called', Environment::variable('HOME', function() {
        return '/home/called';
      }));
    });
  }

  #[@test]
  public function export() {
    with (new EnvironmentSet(['HOME' => null]), function() {
      Environment::export(['HOME' => '/home/test']);
      $this->assertEquals('/home/test', Environment::variable('HOME'));
    });
  }

  #[@test]
  public function unset_variable_by_exporting_with_null() {
    with (new EnvironmentSet(['HOME' => '/home/test']), function() {
      Environment::export(['HOME' => null]);
      $this->assertEquals('/home/default', Environment::variable('HOME', '/home/default'));
    });
  }

  #[@test]
  public function variables() {
    with (new EnvironmentSet(['HOME' => '/home/test']), function() {
      $this->assertEquals('/home/test', Environment::variables()['HOME']);
    });
  }

  #[@test, @values(['/^WITH_.+/', '/^with_.$/i'])]
  public function variables_filtered_by($pattern) {
    with (new EnvironmentSet(['WITH_A' => 'a', 'WITH_B' => 'b', 'NOT_C' => 'c']), function() use($pattern) {
      $this->assertEquals(
        ['WITH_A' => 'a', 'WITH_B' => 'b'],
        Environment::variables($pattern)
      );
    });
  }

  #[@test]
  public function variables_by_names() {
    with (new EnvironmentSet(['OS' => 'Windows_NT', 'HOME' => '/home/test']), function() {
      $this->assertEquals(
        ['OS' => 'Windows_NT', 'HOME' => '/home/test'],
        Environment::variables(['HOME', 'OS'])
      );
    });
  }

  #[@test, @values([
  #  [['TEMP' => 'tmp', 'TMP' => null, 'TMPDIR' => null, 'TEMPDIR' => null]],
  #  [['TEMP' => null, 'TMP' => 'tmp', 'TMPDIR' => null, 'TEMPDIR' => null]],
  #  [['TEMP' => null, 'TMP' => null, 'TMPDIR' => 'tmp', 'TEMPDIR' => null]],
  #  [['TEMP' => null, 'TMP' => null, 'TMPDIR' => null, 'TEMPDIR' => 'tmp']]
  #])]
  public function temp_dir_via_variables($environment) {
    with (new EnvironmentSet($environment), function() {
      $this->assertEquals('tmp'.DIRECTORY_SEPARATOR, Environment::tempDir());
    });
  }

  #[@test]
  public function temp_dir_default() {
    $environment= ['TEMP' => null, 'TMP' => null, 'TMPDIR' => null, 'TEMPDIR' => null];
    with (new EnvironmentSet($environment), function() {
      $this->assertNotEquals('', Environment::tempDir());
    });
  }

  #[@test, @values([
  #  [['HOME' => null, 'APPDATA' => 'dir', 'XDG_CONFIG_HOME' => null]],
  #  [['HOME' => 'dir', 'APPDATA' => null, 'XDG_CONFIG_HOME' => null]],
  #  [['HOME' => 'home', 'APPDATA' => null, 'XDG_CONFIG_HOME' => 'dir']]
  #])]
  public function config_dir_via_variables($environment) {
    with (new EnvironmentSet($environment), function() {
      $this->assertEquals('dir'.DIRECTORY_SEPARATOR, Environment::configDir());
    });
  }

  #[@test]
  public function unix_named_config_dir() {
    with (new EnvironmentSet(['HOME' => 'dir']), function() {
      $this->assertEquals('dir'.DIRECTORY_SEPARATOR.'.test'.DIRECTORY_SEPARATOR, Environment::configDir('test'));
    });
  }

  #[@test]
  public function cygwin_named_config_dir() {
    with (new EnvironmentSet(['HOME' => 'dir', 'APPDATA' => '']), function() {
      $this->assertEquals('dir'.DIRECTORY_SEPARATOR.'.test'.DIRECTORY_SEPARATOR, Environment::configDir('test'));
    });
  }

  #[@test]
  public function windows_named_config_dir() {
    with (new EnvironmentSet(['HOME' => null, 'APPDATA' => 'dir']), function() {
      $this->assertEquals('dir'.DIRECTORY_SEPARATOR.'Test'.DIRECTORY_SEPARATOR, Environment::configDir('test'));
    });
  }

  #[@test]
  public function xdg_named_config_dir() {
    with (new EnvironmentSet(['HOME' => 'home', 'XDG_CONFIG_HOME' => 'dir']), function() {
      $this->assertEquals('dir'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR, Environment::configDir('test'));
    });
  }

  #[@test]
  public function xdg_config_dir_default() {
    with (new EnvironmentSet(['HOME' => 'dir', 'XDG_RUNTIME_DIR' => '/run/user/1000']), function() {
      $this->assertEquals('dir'.DIRECTORY_SEPARATOR.'.config'.DIRECTORY_SEPARATOR, Environment::configDir());
    });
  }
}