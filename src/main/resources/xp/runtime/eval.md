# Evaluates code

* Evaluate code from a command line argument
  ```sh
  $ xp -e 'echo PHP_BINARY'
  ```
* Evaluate code from standard input
  ```sh
  $ echo 'var_dump(1 + 2)' | xp -e
  ```
* Use `--` to separate arguments when piping from standard input
  ```sh
  $ echo 'var_dump($argv[1])' | xp -e -- -a
  ```
* Running [scripts](https://github.com/xp-framework/core/pull/127)
  ```sh
  $ xp -e AgeInDays.script.php 1977-12-24
  ```

Arguments are accessible via *$argv*: `$argv[0]` is the entry point
class, `$argv[1]` is the first argument on the command line, etcetera.
