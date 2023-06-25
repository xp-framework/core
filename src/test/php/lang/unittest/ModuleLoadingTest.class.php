<?php namespace lang\unittest;

use lang\reflect\Module;
use lang\{ClassLoader, ElementNotFoundException};
use unittest\{Assert, Expect, Test};

class ModuleLoadingTest {
  protected $registered= [];

  /**
   * Register a loader with the CL
   *
   * @param  lang.IClassLoader $l
   */
  protected function register($l) {
    $this->registered[]= ClassLoader::registerLoader($l);
  }

  /** @return voud */
  public function tearDown() {
    foreach ($this->registered as $l) {
      ClassLoader::removeLoader($l);
    }
  }

  #[Test]
  public function simple_module() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/simple { }']));
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function leading_php_tag_is_stripped() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagstart { }']));
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function leading_and_trailing_php_tags_are_stripped() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '<?php module xp-framework/tagboth { } ?>']));
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function trailing_php_tag_is_stripped() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/tagend { } ?>']));
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function module_in_namespace() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '<?php namespace lang\unittest;
      module xp-framework/namespaced { 

      }']));
    } finally {
      $this->tearDown();
    }
  }

  #[Test, Expect(['class' => ElementNotFoundException::class, 'withMessage' => '/Missing or malformed module-info/'])]
  public function empty_module_file() {
    try {
      $this->register(new LoaderProviding(['module.xp' => '']));
    } finally {
      $this->tearDown();
    }
  }

  #[Test, Expect(['class' => ElementNotFoundException::class, 'withMessage' => '/Missing or malformed module-info/'])]
  public function module_without_name() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module { }']));
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function loaded_module() {
    try {
      $cl= new LoaderProviding(['module.xp' => 'module xp-framework/loaded { }']);
      $this->register($cl);
      Assert::equals(new Module('xp-framework/loaded', $cl), Module::forName('xp-framework/loaded'));
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function modules_initializer_is_invoked() {
    try {
      $this->register(new LoaderProviding(['module.xp' => 'module xp-framework/initialized {
        public $initialized= false;

        public function initialize() {
          $this->initialized= true;
        }
      }']));
      Assert::equals(true, Module::forName('xp-framework/initialized')->initialized);
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function modules_initializer_is_invoked_once_when_registered_multiple_times() {
    try {
      $tracksInit= new LoaderProviding(['module.xp' => 'module xp-framework/tracks-init {
        public static $initialized= 0;

        public function initialize() {
          self::$initialized++;
        }

        public function initialized() {
          return self::$initialized;
        }
      }']);
      $this->register($tracksInit);
      $this->register($tracksInit);
      Assert::equals(1, Module::forName('xp-framework/tracks-init')->initialized());
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function module_inheritance() {
    try {
      $cl= ClassLoader::defineClass('lang.unittest.BaseModule', Module::class, []);
      $this->register(new LoaderProviding([
        'module.xp' => '<?php module xp-framework/child extends lang\unittest\BaseModule { }'
      ]));
      Assert::equals($cl, typeof(Module::forName('xp-framework/child'))->getParentclass());
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function module_implementation() {
    try {
      $cl= ClassLoader::defineInterface('lang.unittest.IModule', []);
      $this->register(new LoaderProviding([
        'module.xp' => '<?php module xp-framework/impl implements lang\unittest\IModule { }'
      ]));
      $interfaces= typeof(Module::forName('xp-framework/impl'))->getInterfaces();
      foreach ($interfaces as $interface) {
        if ($cl->equals($interface)) return;
      }
      $this->fail($cl->getName().' not included', $interfaces, [$cl]);
    } finally {
      $this->tearDown();
    }
  }

  #[Test]
  public function modules_initializer_can_register_itself_upfront_without_causing_endless_recursion() {
    try {
      $selfUpfront= new LoaderProviding(['module.xp' => 'module xp-framework/self-upfront {
        public function initialize() {
          \lang\ClassLoader::registerLoader($this->classLoader(), true);
        }
      }']);
      $this->register($selfUpfront);
    } finally {
      $this->tearDown();
    }
  }
}