<?php namespace net\xp_framework\unittest\runtime;

use io\{File, Files, Folder};
use lang\{Environment, ElementNotFoundException};
use unittest\{AfterClass, Expect, Test, TestCase, Values};
use xp\runtime\{CouldNotLoadDependencies, Modules};

class ModulesTest extends TestCase {
  private static $cleanup= [];

  /** Creates structure, remembering it for cleanup */
  public static function structure(array $definitions): string {
    $f= self::create(new Folder(Environment::tempDir(), uniqid()), $definitions);
    self::$cleanup[]= $f;
    return $f->getURI();
  }

  /** Creates a file & folder structure based on given definitions */
  private static function create(Folder $f, array $definitions): Folder {
    $f->exists() || $f->create(0777);
    foreach ($definitions as $name => $definition) {
      if (is_array($definition)) {
        self::create(new Folder($f, $name), $definition);
      } else {
        Files::write(new File($f, $name), $definition);
      }
    }
    return $f;
  }

  #[AfterClass]
  public static function cleanup() {
    foreach (self::$cleanup as $folder) {
      $folder->unlink();
    }
  }

  /** Returns a fixture with vendor and user dir structures */
  private function fixture(): Modules {
    return newinstance(Modules::class, [], [
      'autoloaders'  => [],
      'vendorDir'    => self::structure([
        'vendor' => [
          'autoload.php' => '<?php $this->autoloaders[]= "vendor";',
          'xp-framework' => ['core' => ['composer.json' => '{"name": "xp-framework/core"}']],
          'thekid' => ['library' => [
            'composer.json' => '{
              "name": "thekid/library",
              "require": {"xp-framework/core": "^11.0"}
            }'
          ]]
        ]
      ]),
      'userDir'      => self::structure([
        'test' => ['vendor' => [
          'autoload.php' => '<?php $this->autoloaders[]= "user";',
          'thekid' => ['library' => [
            'composer.json' => '{
              "name": "thekid/library",
              "require": {"xp-framework/core": "^10.0 | ^9.0"}
            }'
          ]
        ]]]
      ])
    ]);
  }

  #[Test]
  public function can_create() {
    new Modules();
  }

  #[Test]
  public function modules_initially_empty() {
    $this->assertEquals([], (new Modules())->all());
  }

  #[Test]
  public function adding_a_module() {
    $this->assertEquals(
      ['xp-framework/core' => null],
      (new Modules())->add('xp-framework/core')->all()
    );
  }

  #[Test]
  public function adding_a_module_with_version() {
    $this->assertEquals(
      ['xp-forge/sequence' => '^8.0'],
      (new Modules())->add('xp-forge/sequence', '^8.0')->all()
    );
  }

  #[Test]
  public function adding_modules() {
    $this->assertEquals(
      ['xp-forge/sequence' => '^8.0', 'xp-framework/core' => null],
      (new Modules())->add('xp-forge/sequence', '^8.0')->add('xp-framework/core')->all()
    );
  }

  #[Test]
  public function added_modules_are_uniqued() {
    $this->assertEquals(
      ['xp-framework/core' => null],
      (new Modules())->add('xp-framework/core')->add('xp-framework/core')->all()
    );
  }

  #[Test, Values([['xp-forge/sequence', '^8.0'], ['xp-framework/core', null]])]
  public function module_version($module, $version) {
    $fixture= (new Modules())->add('xp-forge/sequence', '^8.0')->add('xp-framework/core');
    $this->assertEquals($version, $fixture->version($module));
  }

  #[Test, Expect(ElementNotFoundException::class)]
  public function non_existant_module_version() {
    (new Modules())->version('perpetuum/mobile');
  }

  #[Test, Values([null, 'test']), Expect(CouldNotLoadDependencies::class)]
  public function requiring_non_existant_library_in($namespace) {
    $this->fixture()->add('perpetuum/mobile')->require($namespace);
  }

  #[Test, Values([null, 'test'])]
  public function load_library_in($namespace) {
    $fixture= $this->fixture()->add('xp-framework/core')->require($namespace);
    $this->assertEquals(['vendor'], $fixture->autoloaders);
  }

  #[Test]
  public function user_dir_takes_precedence_with_namespace() {
    $fixture= $this->fixture()->add('thekid/library')->require('test');
    $this->assertEquals(['user'], $fixture->autoloaders);
  }

  #[Test]
  public function loads_from_vendor_dir_without_namespace() {
    $fixture= $this->fixture()->add('thekid/library')->require(null);
    $this->assertEquals(['vendor'], $fixture->autoloaders);
  }

  #[Test]
  public function loads_libraries_from_user_and_vendor_dirs() {
    $fixture= $this->fixture()->add('thekid/library')->add('xp-framework/core')->require('test');
    $this->assertEquals(['user', 'vendor'], $fixture->autoloaders);
  }

  #[Test]
  public function does_not_require_autoloader_twice() {
    $fixture= $this->fixture()->add('thekid/library')->add('xp-framework/core')->require(null);
    $this->assertEquals(['vendor'], $fixture->autoloaders);
  }
}