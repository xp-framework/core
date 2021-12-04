<?php namespace net\xp_framework\unittest\runtime;

use io\{File, Files, Folder};
use lang\{Environment, XPClass};
use unittest\{AfterClass, Expect, Test, TestCase};
use xp\runtime\{CouldNotLoadDependencies, Modules};

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

  #[Test]
  public function can_create() {
    new Modules();
  }

  #[Test]
  public function adding_a_module() {
    $fixture= new Modules();
    $fixture->add('xp-forge/sequence', '^8.0');

    $this->assertEquals(['xp-forge/sequence' => '^8.0'], $fixture->all());
  }

  #[Test]
  public function requiring_core_works() {
    $fixture= new Modules();
    $fixture->add('xp-framework/core');

    $fixture->require($namespace= 'test');
  }

  #[Test]
  public function requiring_php_works() {
    $fixture= new Modules();
    $fixture->add('php', PHP_VERSION);

    $fixture->require($namespace= 'test');
  }

  #[Test]
  public function requiring_extension_works() {
    $fixture= new Modules();
    $fixture->add('ext-standard', '*');

    $fixture->require($namespace= 'test');
  }

  #[Test, Expect(CouldNotLoadDependencies::class)]
  public function requiring_non_existant() {
    $fixture= new Modules();
    $fixture->add('perpetuum/mobile');

    $fixture->require($namespace= 'test');
  }

  #[Test]
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

  #[Test, Values([['"test\\\\": "src/"'], ['"test\\\\": "src"'], ['"test": "src/"'], ['"test": "src"']])]
  public function requiring_library_with_psr0_default($definition) {
    $class= 'PSR0D'.crc32($definition);
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'thekid' => ['library' => [
          'composer.json' => '{"autoload": {"psr-0": {'.$definition.'} } }',
          'src' => ['test' => [
            "{$class}.php" => "<?php namespace test; class {$class} {
              public static function loaded() { return true; }
            }"
          ]]
        ]]
      ]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');
    $fixture->require($namespace= 'test');

    try {
      $this->assertTrue((new XPClass("test\\{$class}"))->getMethod('loaded')->invoke(null));
    } finally {
      $loaders= spl_autoload_functions();
      spl_autoload_unregister($loaders[sizeof($loaders) - 1]);
    }
  }

  #[Test, Values([['"test\\\\": "src/"'], ['"test\\\\": "src"'], ['"test": "src/"'], ['"test": "src"']])]
  public function requiring_library_with_psr0_underscore($definition) {
    $class= 'PSR0U'.crc32($definition);
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'thekid' => ['library' => [
          'composer.json' => '{"autoload": {"psr-0": {'.$definition.'} } }',
          'src' => ['test' => ['Impl' => [
            "{$class}.php" => "<?php namespace test; class Impl_{$class} {
              public static function loaded() { return true; }
            }"
          ]]]
        ]]
      ]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');
    $fixture->require($namespace= 'test');

    try {
      $this->assertTrue((new XPClass("test\\Impl_{$class}"))->getMethod('loaded')->invoke(null));
    } finally {
      $loaders= spl_autoload_functions();
      spl_autoload_unregister($loaders[sizeof($loaders) - 1]);
    }
  }

  #[Test, Values([['"test\\\\": "src/"'], ['"test\\\\": "src"'], ['"test": "src/"'], ['"test": "src"']])]
  public function requiring_library_with_psr4($definition) {
    $class= 'PSR4'.crc32($definition);
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'thekid' => ['library' => [
          'composer.json' => '{"autoload": {"psr-4": {'.$definition.'} } }',
          'src' => [
            "{$class}.php" => "<?php namespace test; class {$class} {
              public static function loaded() { return true; }
            }"
          ]
        ]]
      ]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');
    $fixture->require($namespace= 'test');

    try {
      $this->assertTrue((new XPClass("test\\{$class}"))->getMethod('loaded')->invoke(null));
    } finally {
      $loaders= spl_autoload_functions();
      spl_autoload_unregister($loaders[sizeof($loaders) - 1]);
    }
  }

  #[Test]
  public function requiring_library_with_classmap() {
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'composer' => ['autoload_classmap.php' => '<?php
          return ["test\\Test" => dirname(dirname(__FILE__))."/thekid/library/src/Test.php"];'
        ],
        'thekid' => ['library' => [
          'composer.json' => '{"autoload": {"classmap": ["src/"] } }',
          'src' => [
            "Test.php" => "<?php namespace test; class Test{
              public static function loaded() { return true; }
            }"
          ]
        ]]
      ]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');
    $fixture->require($namespace= 'test');

    try {
      $this->assertTrue((new XPClass("test\\Test"))->getMethod('loaded')->invoke(null));
    } finally {
      $loaders= spl_autoload_functions();
      spl_autoload_unregister($loaders[sizeof($loaders) - 1]);
    }
  }

  #[Test, Values([['"ext-standard": "*"'], ['"php": ">=7.0"']])]
  public function requiring_library_with($dependency) {
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'thekid' => ['library' => [
          'composer.json' => '{"require": {'.$dependency.'} }'
        ]]
      ]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');
    $fixture->require($namespace= 'test');
  }

  #[Test, Expect(CouldNotLoadDependencies::class), Values([['"perpetuum/mobile": "^1.0"'], ['"ext-magic": "*"']])]
  public function requiring_library_with_missing($dependency) {
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'thekid' => ['library' => [
          'composer.json' => '{"require": {'.$dependency.'} }'
        ]]
      ]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');

    $fixture->require($namespace= 'test');
  }

  #[Test, Expect(CouldNotLoadDependencies::class)]
  public function requiring_malformed_library() {
    $s= ModulesTest::structure([
      'test' => ['vendor' => [
        'thekid' => ['library' => [
          'composer.json' => 'NOT.JSON'
        ]
      ]]]
    ]);

    $fixture= newinstance(Modules::class, [], ['userDir' => $s]);
    $fixture->add('thekid/library');

    $fixture->require($namespace= 'test');
  }
}