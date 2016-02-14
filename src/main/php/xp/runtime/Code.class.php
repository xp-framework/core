<?php namespace xp\runtime;

/**
 * Wrap code passed in from the command line.
 *
 * @see   https://wiki.php.net/rfc/group_use_declarations
 * @test  xp://net.xp_framework.unittest.runtime.CodeTest
 */
class Code {
  private $fragment, $imports;

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
    $this->imports= [];
    while (0 === strncmp($this->fragment, 'use ', 4)) {
      $delim= strpos($this->fragment, ';');
      foreach ($this->importsIn(substr($this->fragment, 4, $delim - 4)) as $import) {
        $this->imports[]= $import;
      }
      $this->fragment= ltrim(substr($this->fragment, $delim + 1), ' ');
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

  /** @return string */
  public function head() {
    return empty($this->imports) ? '' : 'use '.implode(', ', $this->imports).';';
  }

  /**
   * Returns types used inside a `use ...` directive.
   *
   * @param  string $use
   * @return string[]
   */
  private function importsIn($use) {
    $name= strrpos($use, '\\') + 1;
    $used= [];
    if ('{' === $use{$name}) {
      $namespace= substr($use, 0, $name);
      foreach (explode(',', substr($use, $name + 1, -1)) as $type) {
        $used[]= $namespace.trim($type);
      }
    } else {
      foreach (explode(',', $use) as $type) {
        $used[]= trim($type);
      }
    }
    return $used;
  }
}