# Evaluates code and displays result using var_dump()

* Evaluate code from a command line argument
  ```sh
  $ xp -d 'PHP_BINARY'
  ```
* Evaluate code from standard input
  ```sh
  $ echo '1 + 2' | xp -d
  ```
* Use `--` to separate arguments when piping from standard input
  ```sh
  $ echo '$argv[1]' | xp -d -- -a
  ```

Arguments are accessible via *$argv*: `$argv[0]` is the entry point
class, `$argv[1]` is the first argument on the command line, etcetera.

See also [write](xp help write) and [eval](xp help eval).