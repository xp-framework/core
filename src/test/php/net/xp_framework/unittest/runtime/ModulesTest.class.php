<?php namespace net\xp_framework\unittest\runtime;

use io\{Folder, File, FileUtil};
use lang\Environment;
use unittest\TestCase;
use xp\runtime\{Modules, CouldNotLoadDependencies};

class ModulesTest extends TestCase {
  private static $cleanup= [];

  /** Creates a file & folder structure based on given definitions, remembering it for cleanup */
  public static function structure(array $definitions): string {
    $f= self::create(new Folder(Environment::tempDir(), uniqid()), $definitions);
    self::$cleanup[]= $f;
    return $f->getURI();
  }

  private static function create($f, $definitions) {
    $f->exists() || $f->create(0777);
    foreach ($definitions as $name => $definition) {
      if (is_array($definition)) {
        self::create(new Folder($f, $name), $definition);
      } else {
        FileUtil::write(new File($f, $name), $definition);
      }
    }
    return $f;
  }

  #[@afterClass]
  public static function cleanup() {
    foreach (self::$cleanup as $folder) {
      $folder->unlink();
    }
  }

  #[@test]
  public function can_create() {
    new Modules();
  }

  #[@test]
  public function adding_a_module() {
    $fixture= new Modules();
    $fixture->add('xp-forge/sequence', '^8.0');

    $this->assertEquals(['xp-forge/sequence' => '^8.0'], $fixture->all());
  }

  #[@test]
  public function requiring_core_works() {
    $fixture= new Modules();
    $fixture->add('xp-framework/core');

    $fixture->require($namespace= 'test');
  }

  #[@test]
  public function requiring_php_works() {
    $fixture= new Modules();
    $fixture->add('php', PHP_VERSION);

    $fixture->require($namespace= 'test');
  }

  #[@test, @expect(CouldNotLoadDependencies::class)]
  public function requiring_non_existant() {
    $fixture= new Modules();
    $fixture->add('perpetuum/mobile');

    $fixture->require($namespace= 'test');
  }

  #[@test]
  public function requiring_existing_library() {
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'thekid' => ['library' => [
          'composer.json' => '{
            "require": {
              "xp-framework/core" : "^10.0 | ^9.0"
            }
          }'
        ]
      ]]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');

    $fixture->require($namespace= 'test');
  }
}