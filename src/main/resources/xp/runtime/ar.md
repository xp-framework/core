# Work with XAR archives

* Creates an archive from the directories "src" and "lib" as well as
  the file "etc/config.ini".
  ```sh
  $ xp ar cf app.xar src/ lib/ etc/config.ini
  ```
* Extract all files inside the **app.xar** into the current directory.
  Directories and files are created if necessary, existing files are 
  overwritten.
  ```sh
  $ xp ar xf app.xar
  ```
* List an archive's contents
  ```sh
  $ xp ar tf app.xar
  ```
* Show a single file inside an archive. Always use forward-slashes!
  ```sh
  $ xp ar sf tests.xar unittest/TestSuite.class.php
  ```
* Merge archives
  ```sh
  $ xp ar mf uber.xar app.xar dependencies.xar
  ```

Add *v* to any of the operations, e.g. `xp ar cvf`, to get a more verbose
output of what is happening.