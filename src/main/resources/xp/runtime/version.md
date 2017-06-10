# Shows version and class loader setup

* Basic usage
  ```sh
  $ xp -v
  ```
* Display XP version
  ```sh
  $ xp version xp
  ```
* Display runners version
  ```sh
  $ xp version runners
  ```
* Display PHP version
  ```sh
  $ xp version php
  ```
* Display Engine version - HHVM or Zend
  ```sh
  $ xp version engine
  ```
* Display OS type and version (and distribution, if available)
  ```sh
  $ xp version os
  ```

This command can also be used to verify correct class loader setup. By
including one or more `-cp` and/or `-m` paths before, class path and module
entries are added before the version subcommand is executed.