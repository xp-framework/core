<?php namespace xp\runtime;

/**
 * Shows a given resource from the package this class is contained in
 * on standard error and exit with a given value.
 *
 * ```sh
 * $ xp xp.runtime.ShowResource usage.txt 255
 * ```
 *
 * @deprecated Use xp.runtime.Help instead
 */
class ShowResource {

  /**
   * Main
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    Help::main(['@'.$args[0]]);
    return (int)$args[1];
  }
}
