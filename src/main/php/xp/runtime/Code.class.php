<?php namespace xp\runtime;

use lang\Throwable;

/**
 * Wrap code passed in from the command line.
 *
 * @see   https://wiki.php.net/rfc/group_use_declarations
 * @test  xp://net.xp_framework.unittest.runtime.CodeTest
 */
class Code {
  private $name, $fragment, $modules, $line, $imports, $namespace;

  static function __static() {
    stream_wrapper_register('script', Script::class);
  }

  /**
   * Creates a new code instance
   *
   * @param  string $input
   * @param  string $name
   */
  public function __construct($input, $name= '(unnamed)') {
    $this->name= $name;
    $this->namespace= null;
    $this->modules= new Modules();
    $this->imports= [];

    $pos= 0;
    $length= strlen($input);

    // Shebang
    if ($pos < $length && 0 === substr_compare($input, '#!', $pos, 2)) {
      $pos+= strcspn($input, "\n", $pos) + 1;
    }

    // PHP open tags
    if ($pos < $length && 0 === substr_compare($input, '<?', $pos, 2)) {
      $pos+= strcspn($input, "\r\n\t =", $pos) + 1;
    }

    // Trim whitespace on the left
    $pos+= strspn($input, "\r\n\t ", $pos);

    // Parse namespace declaration
    if ($pos < $length && 0 === substr_compare($input, 'namespace', $pos, 9)) {
      $l= strcspn($input, ';', $pos);
      $this->namespace= substr($input, $pos + 10, $l - 10);
      $pos+= $l + 1;
      $pos+= strspn($input, "\r\n\t ", $pos);
    }

    // Parse imports
    while ($pos < $length && 0 === substr_compare($input, 'use ', $pos, 4)) {
      $l= strcspn($input, ';', $pos);
      foreach ($this->importsIn(substr($input, $pos + 4, $l - 4)) as $import => $module) {
        $this->imports[]= $import;
        $module && $this->modules->add($module);
      }
      $pos+= $l + 1;
      $pos+= strspn($input, "\r\n\t ", $pos);
    }

    $this->fragment= rtrim(substr($input, $pos), "\r\n\t ;").';';
    $this->line= substr_count($input, "\n", 0, $pos > $length ? $length : $pos);
  }

  /** @return string */
  public function fragment() { return $this->fragment; }

  /** @return string[] */
  public function imports() { return $this->imports; }

  /** @return xp.runtime.Modules */
  public function modules() { return $this->modules; }

  /**
   * Returns head including namespace and imports
   *
   * @return string
   */
  public function head() {
    return
      ($this->namespace ? 'namespace '.$this->namespace.';' : '').
      (empty($this->imports) ? '' : 'use '.implode(', ', $this->imports).';')
    ;
  }

  /**
   * Returns a new instance of this code instance, with a `return` statement
   * inserted if necessary.
   *
   * @return self
   */
  public function asExpression() {
    $self= clone $this;
    if (!strstr($self->fragment, 'return ') && !strstr($self->fragment, 'return;')) {
      $self->fragment= 'return '.$self->fragment;
    }
    return $self;
  }

  /**
   * Returns types and modules used inside a `use ...` directive.
   *
   * @param  string $use
   * @return [:string]
   */
  private function importsIn($use) {
    if (false === ($p= strpos($use, ' from '))) {
      $module= null;
    } else {
      $module= trim(substr($use, $p + 6), '"\'');
      $use= substr($use, 0, $p);
    }

    $name= strrpos($use, '\\') + 1;
    $used= [];
    if ('{' === $use{$name}) {
      $namespace= substr($use, 0, $name);
      foreach (explode(',', substr($use, $name + 1, -1)) as $type) {
        $used[$namespace.trim($type)]= $module;
      }
    } else {
      foreach (explode(',', $use) as $type) {
        $used[trim($type)]= $module;
      }
    }
    return $used;
  }

  /**
   * Runs an expression of code in the context of this code
   *
   * @param  string[] $argv
   * @return int
   * @throws lang.Throwable
   */
  public function run($argv= []) {
    $this->modules->require();

    Script::$code[$this->name]= '<?php '.$this->head().str_repeat("\n", $this->line).$this->fragment."\nreturn null;";
    try {
      $argc= sizeof($argv);
      return include('script://'.$this->name);
    } catch (\Throwable $t) {
      throw Throwable::wrap($t);
    } finally {
      unset(Script::$code[$this->name]);
    }
  }
}