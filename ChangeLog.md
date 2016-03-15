XP Framework Core ChangeLog
========================================================================

## 6.3.7 / 2015-03-15

* Cherry picked: Declare static initializer in a MockProxy
  (@beorgler, @haimich, @kiesel)
* Fixed VERSION-string in `src/main/resources/VERSION`
  (@kiesel)
* Cherry picked: Fix syntax errors (in unittest/web/{SelectOption,WebTestCase})
  (@kiesel, originally from @friebe)
* Cherry picked: Fix unittest.web.SelectField::setValue() double-encoding UTF8
  (@kiesel, originally from @friebe)
* Cherry picked: Only run tests if dependencies are loaded
  (@kiesel, originally from @friebe)
* Cherry picked: Fix DOM not being loaded on HHVM
  (@kiesel, originally from @friebe)
* Cherry picked: Fix util.log.SyslogAppender problems with multiple instances
  (@kiesel, originally from @friebe)
* Cherry picked: Fix uriFor() raising exceptions
  (@kiesel, originally from @friebe)
* Cherry picked: Remove uses()-related tests
  (@kiesel, originally from @friebe)


## 6.3.6 / ????-??-??
* Cherry picked: Fix "Interface `io\streams\SocketOutputStream` not found"
  (@kiesel, originally from @friebe)


## 6.3.0 / 2015-06-02

### Heads up!

* Added a new `nameof()` core function aimed at replacing the getClassName()
  method and deprecated the \xp::nameOf() method.
  (@thekid)
* Deprecated wrapper types in `lang.types`, primitive boxing and unboxing.
  See xp-framework/core#84
  (@thekid)

### RFCs

* Implemented first part of xp-framework/rfc#297: Added new `lang.Value`
  interface and support for it throughout the framnework as a forward-
  compatibility measure. See xp-framework/core#85
  (@thekid)

#### Bugfixes

* Fixed `unittest.actions.VerifyThat` not running static methods correctly
  (@thekid)

### Features

* Added util.Objects::compare() method which mimics PHP7's `<=>` operator
  including support for lang.Value::compareTo().
  (@thekid)
* Merged xp-framework/core#76: Implement type unions. Instead of using
  the `var` type on methods following the *be liberal in what you accept*
  rule and documenting what is accepted in the apidocs, the type system
  now allows for types such as `int|string`. This is widely used in PHP
  pseudo code, and may even end up in syntax if the union types RFC gets
  accepted: https://wiki.php.net/rfc/union_types
  (@thekid)

## 6.2.5 / 2015-05-25

### Heads up!

* Added class constants for Archive::open() and deprecated `ARCHIVE_*`.
  See xp-framework/core#80
  (@thekid)
* Added class constants for File::open() and deprecated `FILE_MODE_*`.
  See xp-framework/core#79
  (@thekid)
* Deprecated io.ZipFile class - use GzDecompressingInputStream instead!
  See xp-framework/core#78
  (@thekid)
* Deprecated io.SpoolDirectory class
  (@thekid)

### Features

* Merged xp-framework/core#83: New io.Path::real() shorthand
  (@thekid)
* Merged xp-framework/core#82: Path::asFile() and asFolder() prevent
  conditionals
  (@thekid)
* Merged xp-framework/core#81: Feature: Folder entries
  (@thekid)
* Changed io.Path::equals() to perform normalization before comparing
  (@thekid)
* Changed io.File::open() to return the file itself, enabling fluent API
  usage: `$f= (new File('test'))->open(File::READ);`.
  (@thekid)
* Changed io.streams.TextWriter to accept output streams or I/O channels
  as its constructor argument, and io.streams.TextReader to accept input
  streams, strings or I/O channels as their constructor arguments.
  (@thekid)
* Merged xp-framework/core#77: Text reader iteration
  (@thekid)

## 6.2.4 / 2015-05-23

### Heads up!

* Deprecated XPI (XP Installer). Use Composer or Glue instead.
  (@thekid)

### Features

* Merged xp-framework/core#75: Support for new `unittest -w` command line.
  See https://github.com/xp-framework/xp-runners/releases/tag/v5.3.0
  (@thekid)
* Made `unittest -e` consistent with `xp -e`: Add ability to omit trailing
  semicolon, support leading opening PHP tag, code piped via stdin.
  (@thekid)

## 6.2.3 / 2015-05-18

### Heads up!

* Deprecated RandomCodeGenerator and RandomPasswordGenerator classes from
  the security.password package
  (@thekid)
* Deprecated io.sys.StdStream class, superseded by io.streams API
  (@thekid)
* Deprecated `text.format` and `text.parser` APIs.
  (@thekid)
* Deprecated io.SearchableStream class, superseded by text.Tokenizer API
  (@thekid)
* Deprecated security.Policy class and API
  (@thekid)

### Features

* Made ResourceProvider useable via `new import("lang.ResourceProvider")`.
  This way, you don't need to force its loading by adding e.g. a call to
  XPClass::forName() to the static initializer / constructor / etcetera.
  (@thekid)
* Made typeof() behave consistent with reflection on callable type hint.
  (@thekid)
* Made util.log.LogCategory's first parameter optional, using "default"
  as identifier if omitted.
  (@thekid)

### Bugfixes

* Fixed `is()` to support function types correctly
  (@thekid)
* Fixed `Objects::hashOf()` raising an exception when invoked w/ functions
  (@thekid)
* Fixed `xp::stringOf()` producing incorrect indentation for values nested
  inside arrays
  (@thekid)

## 6.2.2 / 2015-05-17

### Bugfixes

* Fixed class detail parsing for function types
  (@thekid)
* Fixed Type::forName() to also work for the `callable` type union.
  (@thekid)
* Fixed issue #74: Ambiguity in function types
  (@thekid)

## 6.2.1 / 2015-05-14

### Features

* Merged xp-framework/core#73: Optimized reflection details for exception
  class being initialized multiple times.
  (@thekid, @kiesel)

## 6.2.0 / 2015-05-05

### Heads up!

* Changed `lang.XPClass` to load the reflection instance lazily. This way,
  we can defer class loading until we actually access a details requiring
  the reflection instance; and thus speed up iterating a packages' classes,
  for instance.
  (@thekid)
* Deprecated `null()` core functionality and rewrote codebase to do
  without it. See pull request xp-framework/core#70
  (@thekid)
* Deprecated `delete()` core functionality - use the `unset` language
  construct instead. See pull request xp-framework/core#69
  (@thekid)

### Features

* Merged xp-framework/core#71: Unicode unittest icons - @thekid

## 6.1.1 / 2015-04-25

### Bugfixes

* Fixed thrown exceptions not appearing in `xp -r` output for interface
  (@thekid)

### Features

* Replaced all occurrences of `create_function()` with real closures
  (@thekid)
* Added support for `/** @type [type] */` for fields.
  (@thekid)

## 6.1.0 / 2015-04-06

### Heads up!

* Deprecated uses() core functionality - use the `use` statement and PHP's
  namespaces instead. This also deprecates classes in the global namespace
  and the "package"-classes introduced in xp-framework/rfc#37.
  (@thekid)
* Deprecated raise() core functionality - use the `throw` statement
  instead, it also uses lazy classloading for namespaced classes.
  (@thekid)
* Removed deprecated support for "mixed", "char", "*", "array<T>" and
  "array<string, string>"' in Type::forName(), see xp-framework/core#64
  (@thekid)
* Removed peer.server.Server::addListener() and related classes which had
  been deprecated since August 2006. See pull request xp-framework/core#63
  (@thekid)
* Deprecated `lang.ClassLoader::defineType(string, string, var)` usage.
  Its second parameter now expects a map containing "kind" (either "class",
  "trait" or "interface"), "extends", "implements" and "use" (arrays of
  type references - either XPClass instances or strings).
  (@thekid)

### RFCs

* Implemented relevant part of xp-framework/rfc#298: The `getClassName()`
  method is now deprecated. Rewrite code to use `nameof($this)`.
  (@thekid)
* Implemented xp-framework/rfc#292: Create anonymous instances from traits
  with `newinstance()`. See pull request xp-framework/core#60
  (@thekid)

### Features

* Added support for `/** @var [type] */` for fields.
  http://www.phpdoc.org/docs/latest/references/phpdoc/tags/var.html
  (@thekid)
* Added `lang.XPClass::isTrait()` and `lang.XPClass::getTraits()` methods.
  (@thekid)

### Bugfixes

* Ensured modules are only initialized once - @thekid

## 6.0.1 / 2015-02-27

### Bugfixes

* Added code compensating for [removed hex support in strings](https://wiki.php.net/rfc/remove_hex_support_in_numeric_strings) in PHP7  - @thekid
* Added GzDecompressingInputStream::header() method - @thekid
* Fixed GzDecompressingInputStream not supporting gzip data with embedded
  original filenames - @thekid

## 6.0.0 / 2015-02-08

### Heads up!

* Added experimental support [HHVM](http://hhvm.com/) support. The version
  tested successfully while writing this is 3.6.0-dev. See xp-framework/core#56
  (@thekid)
* Added PHP 7.0 forward compatibility for the Catchable "Call to a member 
  function" [functionality](https://github.com/php/php-src/pull/847)
  and throw a `NullPointerException`.
  (@thekid)
* Made xp-framework available [via Composer](https://packagist.org/packages/xp-framework/core)
  (@thekid)
* Refactor fatal error handling, see xp-framework/core#30 - (@thekid)
* Changed Console class to throw exceptions if read and/or write operations
  are invoked outside of a console environment - (@thekid, @kiesel)
* Removed deprecated `lang.Enum::membersOf()` method - (@thekid)
* Removed deprecated static getProxyClass() and newProxyInstance() 
  methods from the unittest.mock.MockProxyBuilder class - (@thekid)
* Removed deprecated methods in lang.archive.Archive:
  - addFileBytes() - replaced by addFile()
  - add() - replaced by addBytes()
  (@thekid)
* Removed deprecated methods in util.Date:
  - mktime() - replaced by create()
  - fromString() - handled by regular constructor
  (@thekid)
* Removed deprecated add*() methods in util.TimeSpan class - (@thekid)
* Removed support for deprecated multi-value annotations - (@thekid)
* Removed deprecated lang.ChainedException - (@thekid)
* Removed deprecated text.CSVGenerator and text.parser.CSVParser classes.
  Use https://github.com/xp-framework/csv instead - (@thekid)
* Removed deprecated Primitive::$[BOOLEAN, INTEGER] and Type::$ANY- (@thekid) 
* Removed deprecated lang.types.Number::floatVal() - (@thekid)
* **Minimum PHP version reqired is now PHP 5.4.0** - (@thekid)
* Removed deprecated `assertArray()`, `assertObject()`, `assertEmpty()`,
  `assertNotEmpty()`, `assertClass()` and `assertSubclass()` methods from
  unittest.TestCase - (@thekid)
* Changed xp::ENCODING to 'utf-8', all strings are now per default to be
  regarded as containing this charset except where explicitely stated
  otherwise! - (@thekid)
* Classes are now only referenceable by their namespaced names except
  for those in the `lang` package (and subpackages thereof) - (@thekid)
* Removed support for `__generic` style generics which have been deprecated
  since the implementation of RFC #193 - (@thekid)
* Removed deprecated `xp::registry()` function, which has been superseded
  by the static `xp::$registry` member. Continue considering this a core
  internal API, though! (@thekid)
* Removed obsolete top-level `gui` package - (@thekid)
* Removed deprecated `ref()` and `deref()` functionality - (@thekid)
* Moved classes inside text.util - Random(Code|Password)Generator to the
  package security.password - (@thekid)
* Moved classes inside scriptlet.rpc to webservices.rpc - (@thekid)

### RFCs

* Implemented RFC #291: Stricter error handling (@thekid)
* Implemented RFC #289: `Stream` class deprecation, introduce `Channel`
  (@thekid)
* Implemented RFC #290: New Path class (@thekid)
* Implemented RFC #288: Deprecate LONG_MIN / LONG_MAX (@thekid)
* Implemented RFC #287: Get rid of tools (@thekid)
* Implemented RFC #184: ArrayMap and ArrayList - (@thekid)
* Implemented RFC #286: Function types - (@thekid)
* Implemented RFC #283: Unittest closure actions - (@thekid)
* Implemented RFC #276: Define classes with annotations - (@thekid)
* Implemented RFC #282: Generic type variables - (@thekid)
* Implemented RFC #098: Generic Filter interface - (@thekid)
* Implemented RFC #266: Extend the XP typesystem - (@thekid)
* Implemented RFC #281: PHP 5.4.0 - (@thekid)
* Implemented RFC #146: Unicode - (@thekid)
* Implemented RFC #136: PHP namespaces adoption. All classes in the XP
  framework are now in PHP 5.3 namespaces - (@thekid)
* Implemented RFC #136: PHP namespaces adoption. All classes in the XP
  framework are now in PHP 5.3 namespaces - (@thekid)
* Implemented RFC #279: Newinstance with closures in xp-framework/core#2
  (@thekid)
* Implemented RFC #186: Drop SAPI feature alltogether; after it was 
  deprecated since 5.9.0 - (@thekid)
* Implemented RFC #262: Split up framework into minimal pieces:
  - Extracted `util.telephony` into https://github.com/xp-framework/telephony
  - Extracted `xml` into https://github.com/xp-framework/xml
  - Extracted `xp.codegen` into https://github.com/xp-framework/codegen
  - Extracted `peer.webdav` into https://github.com/xp-framework/webdav
  - Extracted `peer.sieve` into https://github.com/xp-framework/sieve
  - Extracted `peer.irc` into https://github.com/xp-framework/irc
  - Extracted `peer.news` into https://github.com/xp-framework/news
  - Extracted `peer.mail` into https://github.com/xp-framework/mail
  - Extracted `peer.ldap` into https://github.com/xp-framework/ldap
  - Extracted `peer.http` into https://github.com/xp-framework/http
  - Extracted `peer.ftp` into https://github.com/xp-framework/ftp
  - Extracted `img` into https://github.com/xp-framework/imaging
  - Extracted `scriptlet` into https://github.com/xp-framework/scriptlet
  - Extracted `webservices` into https://github.com/xp-framework/webservices
  - Extracted `webservices.rest` into https://github.com/xp-framework/rest
  - Extracted `rdbms` into https://github.com/xp-framework/rdbms
  - Extracted `io.archive.zip` into https://github.com/xp-framework/zip
  - Extracted `text.spelling` into https://github.com/xp-framework/spelling
  - Extracted `text.parser` into https://github.com/xp-framework/parser
  - Extracted `text.csv` into https://github.com/xp-framework/csv
  - Extracted `text.doclet` into https://github.com/xp-framework/doclet
  - Extracted `remote` into https://github.com/xp-framework/remote
  (@kiesel, @thekid)

### Bugfixes

* Fixed problem with enum member auto-initialization and non-public static
  properties.
  (@thekid)
* Fixed xp-framework/core#38: Use of undefined constant STR_ENC- (@thekid)
* Fixed xp-framework/core#37: var not assignable from var?! - (@thekid)
* Fixed xp-framework/core#34: FunctionType doesn't load classes - (@thekid)
* Fixed xp-framework/core#32: Warning in String::endsWith() - (@thekid)
* Fixed xp-framework/core#20: Generic classes and namespaces - (@thekid)
* Fixed `io.streams.Streams` instances to return true for `is_file()`
  (@thekid)
* Fixed `BufferedInputStream::available()` (see xp-framework/xp-runners#17)
  (@thekid)
* Fixed closures inside objects and arrays leading to xp::stringOf() raising
  an exception (Serialization of 'Closure' is not allowed) - (@thekid)
* Fixed xp-framework/xp-framework#347 - "Undefined variable: len" in BSDSocket
  (@haimich)

### Features

* Added support for `::class` in annotations - xp-framework/core#52 (@thekid)
* Made io.streams.MemoryOutputStream implement io.streams.Seekable - (@thekid)
* Implemented support for expanding environment variables in property files.
  See PR #42 and xp-framework/xp-framework#365 (@thekid, @johannes85)
* Added support for `callable` typehint - (@thekid)
* Changed util.profiling.Timer to be able to provide intermediate results
  with `elapsedTime()` without prior call to `stop()`, and added fluent
  interface to util.profiling.Timer's start() and stop() methods - (@thekid)
* Implemented taking exceptions from tearDown() into account for test failure / 
  success in the unittest package, see xp-framework/core#32 - (@thekid)
* Implemented pushing back bytes to buffered stream (see xp-framework/core#16)
  (@thekid)
* Added support for closures in annotations - xp-framework/core#7 - (@thekid)
* Merged xp-framework/xp-framework#353: Add support for rolling logfile names 
  in FileAppender (and Logger) - (@thekid, @kiesel)
* Changed Console class to print `true` and `false` for booleans instead of
  `1` for true and an empty string for false - (@thekid)
* Implemented generic util.log.LogCategory::log($level, $args)
  See xp-framework/core#4 - (@thekid)
* Added util.ConfigurationException and util.ApplicationException as requested
  in xp-framework/xp-framework#346 - (@thekid)
* Added support for `xpcli -?` for consistency reasons - (@thekid)
* Extended the `with` statement to to work with lang.Closeable instances.
  See xp-framework/core#2 - (@thekid)
