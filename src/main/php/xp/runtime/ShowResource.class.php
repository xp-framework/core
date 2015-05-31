<?php namespace xp\runtime;

use util\cmd\Console;

/**
 * Shows a given resource from the package this class is contained in
 * on standard error and exit with a given value.
 *
 * ```sh
 * $ xp xp.runtime.ShowResource usage.txt 255
 * ```
 */
class ShowResource extends \lang\Object {

  /**
   * Main
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    Console::$err->writeLine((new \lang\XPClass(__CLASS__))->getPackage()->getResource($args[0]));
    return (int)$args[1];
  }
}
