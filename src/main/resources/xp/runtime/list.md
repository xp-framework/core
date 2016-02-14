# Lists subcommands

* Shows builtins, locally available and globally installed subcommands:
  ```sh
  $ xp list
  ```
* Load module and include subcommands defined therein:
  ```sh
  $ xp -m ~/devel/xp/compiler list
  ```

Globally installed subcommands are found in Composer's global target
[COMPOSER_HOME](https://getcomposer.org/doc/03-cli.md#composer-home),
locally available ones by checking the module path for `vendor/bin`.

To provide a subcommand alongside your library or in your project,
see [here](https://github.com/xp-runners/reference#plugin-architecture)