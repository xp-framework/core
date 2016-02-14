# Evaluates code and displays result using Console::writeLine()

* Evaluate code from a command line argument
  ```sh
  $ xp -w 'PHP_BINARY'
  ```
* Evaluate code from standard input
  ```sh
  $ echo '1 + 2' | xp -w
  ```
* Use `--` to separate arguments when piping from standard input
  ```sh
  $ echo '$argv[1]' | xp -w -- -a
  ```

Arguments are accessible via *$argv*: `$argv[0]` is the entry point
class, `$argv[1]` is the first argument on the command line, etcetera.

See also [dump](xp help dump) and [eval](xp help eval).