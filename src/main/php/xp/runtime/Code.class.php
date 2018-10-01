<?php namespace xp\runtime;

use lang\Throwable;

/**
 * Wrap code passed in from the command line.
 *
 * @see   https://wiki.php.net/rfc/group_use_declarations
 * @test  xp://net.xp_framework.unittest.runtime.CodeTest
 */
class Code {
  private $fragment;
  private $imports= [];
  private $modules= [];
  private $namespace= null;

  /**
   * Creates a new code instance
   *
   * @param  string $input
   */
  public function __construct($input) {

    // Shebang
    if (0 === strncmp($input, '#!', 2)) {
      $input= substr($input, strcspn($input, "\n") + 1);
    }

    // PHP open tags
    if (0 === strncmp($input, '<?', 2)) {
      $input= substr($input, strcspn($input, "\r\n\t =") + 1);
    }

    $this->fragment= trim($input, "\r\n\t ;").';';

    if (0 === strncmp($this->fragment, 'namespace', 9)) {
      $length= strcspn($this->fragment, ';', 10);
      $this->namespace= substr($this->fragment, 10, $length);
      $this->fragment= ltrim(substr($this->fragment, 11 + $length), "\r\n\t ");
    }

    $this->modules= new Modules();
    while (0 === strncmp($this->fragment, 'use ', 4)) {
      $delim= strpos($this->fragment, ';');
      foreach ($this->importsIn(substr($this->fragment, 4, $delim - 4)) as $import => $module) {
        $this->imports[]= $import;
        $module && $this->modules->add($module, $import);
      }
      $this->fragment= ltrim(substr($this->fragment, $delim + 1), "\r\n\t ");
    }
  }

  /** @return string */
  public function fragment() { return $this->fragment; }

  /** @return string */
  public function expression() {
    return strstr($this->fragment, 'return ') || strstr($this->fragment, 'return;')
      ? $this->fragment
      : 'return '.$this->fragment
    ;
  }

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
   * @param  string $expression
   * @param  string[] $argv
   * @return int
   * @throws lang.Throwable
   */
  public function run($expression, $argv= []) {
    $this->modules->require();

    $argc= sizeof($argv);
    try {
      return eval($this->head().$expression);
    } catch (\Throwable $t) {
      throw Throwable::wrap($t);
    }
  }
}