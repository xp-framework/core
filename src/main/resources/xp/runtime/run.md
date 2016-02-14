# Run

* Run a class' main() method
  ```sh
  $ xp run com.example.Test
  ```
* Run is the default subcommand, so it can be omitted
  ```sh
  $ xp com.example.Test
  ```

By supplying the `run` command explicitely, all ambiguities between class
and script names and subcommands can be avoided. Usually, these do not
clash, though, so it can be omitted.

See also the [xp command usage](xp help).
