XP Framework Core ChangeLog
========================================================================

## ?.??.? / ????-??-??

## 6.10.0 / 2016-01-10

### RFCs

* Integraded support for new XP runners defined in xp-framework/rfc#303.
  (@thekid)
* Implemented xp-framework/rfc#307: Extract XPCLI from core. The new
  package lives in https://github.com/xp-framework/command
  @thekid

### Features

* Merged xp-framework/core#114: Make paths displayed in class loaders'
  `toString()` copy&pasteable
  (@thekid)
* Added `xp.runtime.Help` with support for basic markdown. It is used by
  the *xp help*  subcommand, and exports a public API for rendering. See
  xp-runners/reference#5, xp-framework/unittest#14 and xp-framework/command#1
  (@thekid)

## 6.9.2 / 2016-01-05

### Bugfixes

* Fixed util.Objects to handle instances of non-XP classes in equal(),
  compare() and hashOf(). See xp-framework/core#113
  (@thekid)

## 6.9.1 / 2015-12-28

### Bugfixes

* Fix wrapping \Exceptions behavior difference between PHP5 and PHP7
  (@thekid)
* Made `lang.XPClass::forName()` accept native class names, also
  (@thekid)

## 6.9.0 / 2015-12-24

### Heads up!

* **Heads up: Removed deprecated ensure()**, which has been replaced
  by the `finally` statement. See xp-framework/core#111
  (@thekid)

### RFCs

* Implemented xp-framework/rfc#305: with() results. See pull request #112
  @thekid

### Bugfixes

* Fixed xp-framework/core#110: Fatal error location swallowed - @thekid
* Catch both PHP 5 and PHP 7 base exceptions in lang.Thread - @thekid

### Features

* Added static `lang.Throwable::wrap()` to wrap any exception, including
  PHP 5 and PHP 7's native base exceptions, in a `lang.Throwable` instance
  (@thekid)
* Changed `lang.Throwable` to accept any other instance of itself as
  cause *as well as* PHP 5 and PHP 7's native base exceptions.
  (@thekid)

## 6.8.0 / 2015-12-20

### Heads up!

* **Heads up: Removed classes deprecated since XP 6.3.0:**
  . `util`: util.StringUtil
  . `io`: Stream, ZipFile, SearchableStream, SpoolDirectory, FilePermission
  . `io.sys`: StdStream
  . `security`: Permission, Policy, PolicyException
  . `security.password`: RandomPasswordGenerator, RandomCodeGenerator
  (@thekid)

### Bugfixes

* Fixed `util.Objects::compare()` consistency with PHP7's `<=>` operator
  for arrays and maps.
  (@thekid)
* Fixed `lang.reflect.Constructor` to catch PHP7's native `\Throwable`
  class instead of `\BaseException` class, which was the root class before
  [the Throwable RFC](https://wiki.php.net/rfc/throwable-interface).
  (@thekid)

### Features

* Refactored `util.Properties` to work without Tokenizer API, which has
  been extracted to its own package in 6.7.0
  (@thekid)
* Merged PR #108: security.SecureString -> `util.Secret`
  (@thekid, @mikey179, @kiesel)

## 6.7.0 / 2015-12-09

### RFCs

* Implemented next part of xp-framework/rfc#296: Remove xp::reflect()
  @thekid
* Implemented more parts of xp-framework/rfc#296:
  . Extracted the `peer.*` APIs to its own package and deprecated the
    one inside core. See https://github.com/xp-framework/networking
  . Extracted the `math` API to its own package and deprecated the
    one inside core. See https://github.com/xp-framework/math
  . Extracted the `text` API to its own package and deprecated the
    one inside core. Split into:
    . https://github.com/xp-framework/tokenize
    . https://github.com/xp-framework/patterns
    . https://github.com/xp-framework/text-encode
  . Extracted the `security` API to its own package and deprecated the
    one inside core. See https://github.com/xp-framework/security
  (@thekid)

### Bugfixes

* Fixed `lang.reflect.Method` to use PHP's type information if available
  but consistently prefer the apidocs for the reason stated below.
  (@thekid)
* Fixed `lang.reflect.Parameter` to only use PHP's type information if
  no apidoc is present. The reason is that we might have much "richer"
  information, e.g. a parameter typed `string[]` (whereas PHP would 
  only know about an array of whatever).
  (@thekid)

## 6.6.0 / 2015-11-23

### RFCs

* Implemented next part of xp-framework/rfc#297: Merged PR #100
  (@thekid)
* Implemented next part of xp-framework/rfc#298:
  . Removed deprecated `xp::null()`
  . Removed deprecated `xp::nameOf()`
  . Removed deprecated `xp::error()` and replace by exceptions
  . Removed deprecated `this()` - replaced by syntactic support
  (@thekid)

### Bugfixes

* Fixed fatal errors when parsing URLs w/ hashes.
  See xp-framework/xp-framework#380
  (@patzerr)
* Fixed "Call to undefined method lang.FunctionType::isGeneric()"
  in lang.WildcardType (occurred in the sequence library when
  using the current development checkout)
  (@thekid)
* Fixed FunctionType not handling varargs correctly - @thekid

### Features

* Merged PR #107: Variadic parameter support in reflection
  (@thekid)

## 6.5.6 / 2015-10-25

### Bugfixes

* Fixed XPClass not searching traits for member declarations - @thekid

## 6.5.5 / 2015-10-07

### Features

* Made reflection resolve unqualified classes inside api doc via imports
  (@thekid)
* Changed reflection to support fully-qualified class names inside api
  doc tags for fields, methods and constructors: `@param \util\Date $param`,
  `@return \util\Date`, `@throws \lang\Throwable` and `@var \util\Date`.
  (@thekid, @mikey179)
* Changed `ClassLoader::defineType()` to also support native type notation
  with backslashes inside its first argument.
  (@thekid, @mikey179)

## 6.5.4 / 2015-09-27

### Features

* Extracted the `io.collections` API to its own package and deprecated the
  one inside core. See https://github.com/xp-framework/io-collections
  (@thekid)

### Bugfixes

* Fixed `xp -r T` for when *T* refers to a trait - @thekid

## 6.5.3 / 2015-09-27

### Features

* Merged #102: Add in() and out() methods to io.collections - @thekid
* Merged #103: Refactor: Use ::class - @thekid
* Merged #104: Show traits w/ trait keyword, w/o abstract - @thekid

## 6.5.2 / 2015-09-04

### Features

* Created an XP Framework release from an airplane - @thekid
* Cleaned up deprecated "mixed" type in favor of "var" - @thekid

## 6.5.1 / 2015-08-24

### Bugfixes

* Fixed `peer.SocketOutputStream` declaration - @thekid
* Fixed `lang.FunctionType` to work as part of a generic type - @thekid
* Merged xp-framework/core#101: Make collections API work with closures
  (@thekid)
* Fixed `util.Objects::hashOf()` for arrays and maps containing closures
  (@thekid)
* Fixed lang.types.ArrayList and lang.types.ArrayMap's equals() methods
  in conjunction with lang.Value instances.
  (@thekid)

## 6.5.0 / 2015-08-22

### Heads up!

* **Heads up: Bumped the minimum PHP version requirement to PHP 5.5!**
  . Rewrote test code base to use `finally` instead of ensure (see #97)
  . Rewrote test code base to use `::class` where applicable (see #98)
  (@thekid)
* **Deprecated the `ensure` core functionality**. It was a future-ready
  replacement for the finally statement introduced with PHP 5.5
  (@thekid)
* **Removed deprecated `delete` core functionality**. Instead, use the
  language construct `unset()`.
  (@thekid)
* **Removed deprecated `text.format` and `text.parser` packages**.
  (@thekid)
* **Removed deprecated and dysfunctional XP installer**. Instead, use
  [Composer](https://getcomposer.org/)
  (@thekid)

### RFCs

* Implemented first part of xp-framework/rfc#301: Extracted `util.log`
  package into its own library - https://github.com/xp-framework/logging.
  The classes remain in core until 7.0, the library serves as an overlay.
  (@thekid)
* Implemented first part of xp-framework/rfc#293: Extracted `unittest`
  package into separate libraries:
  . https://github.com/xp-framework/unittest 
  . https://github.com/xp-framework/mocks 
  . https://github.com/xp-framework/webtest 
  The classes remain in core until 7.0, the libraries serve as an overlay.
  (@thekid)

### Bugfixes

* Fixed util.log.SyslogAppender problems with multiple instances logging
  to the same identifier and facility.
  (@mikey179, @thekid)

### Features

* Implemented setting log appender's layout via ini file
  (@thekid, @mikey179)

## 6.4.2 / 2015-08-05

### Bugfixes

* Fixed issue #96: Endless recursion when registering own classloader in
  front of class path inside a module. This is useful when a module
  provides a so-called "core overlay".
  (@thekid)

## 6.4.1 / 2015-08-05

### Bugfixes

* Fixed `unittest.XmlTestListener::uriFor()` raising exceptions - @thekid
* Fixed WebTestCase test class using obsolete `assertClass()` - @thekid
* Fixed `SelectField::setValue()` double-encoding UTF8 - @thekid
* Fixed syntax errors in `unittest.web` package  - @thekid

## 6.4.0 / 2015-07-12

### Heads up!

* **Deprecated long array syntax** (for annotations and in code)
  See xp-framework/core#93
  (@thekid)
* **Deprecated this() core functionality**.
  See xp-framework/core#92
  (@thekid)
* **Removed variant of create() which returns object given**.
  See xp-framework/core#91
  (@thekid)
* **Removed deprecated raise() core functionality**.
  See xp-framework/core#89
  (@thekid)
* **Removed pre-namespace class loading**:
  - The deprecated `uses()` core functionality has been removed.
  - It is no longer possible to use package-qualified classes.
  - Using classes declared in the global namespace as fully-qualified is
    also no longer supported.
  See xp-framework/core#88
  (@thekid)

### Features

* Merged xp-framework/core#94: Use "use" statements in commandline
  (@thekid)

## 6.3.4 / 2015-06-25

### Heads up!

* **Dropped support for PHP7 alpha1, now requires alpha2!** This second
  alpha includes the "Throwable" RFC which changes the builtin exception
  hierarchy.
  (@thekid)

### Bugfixes

* Adopted to changes in PHP7's exception hierarchy. See php/php-src#1284
  and https://wiki.php.net/rfc/throwable-interface
  (@thekid)
* Changed `lang.CommandLine`, `text.TextTokenizer`, `text.StreamTokenizer`
  and `io.streams.StringReader` to cope with a behaviour change to substr.
  See https://bugs.php.net/69931.
  (@thekid)
* Fixed bug with PHP7 when reading lines from the console
  (@thekid)
* Fixed bug with importing into global scope on HHVM
  (@thekid)

## 6.3.3 / 2015-06-24

* Fixed bug when using `uses()` with classes that have an __import method.
  (@johannes85)

## 6.3.2 / 2015-06-23

### Bugfixes

* Fixed forking server to handle *SIGTERM* correctly, which is what xpws
  sends when the user presses Enter.
  (@thekid)
* Fixed race condition inside `peer.server.PreforkingServer` which would
  prevent a clean shutdown
  (@thekid)
* Fixed `math.BigNum` division on HHVM cutting off below default precision
  (@thekid)

### Features

* Added `toString()` method to *BigNum* class (and thus BigInt and BigFloat)
  (@thekid)

## 6.3.1 / 2015-06-13

### Heads up!

* This release creates forward compatibility with PHP7. Please note the
  XP6 series will not support PHP7 officially: It reserves the name `object`,
  which clashes with our root class `lang.Object`.

  However, [PHP7 alpha 1](http://php.net/archive/2015.php#id2015-01-11-6)
  does [not yet raise an error](https://github.com/php/php-src/blob/php-7.0.0alpha1/UPGRADING#L392)
  if this class name is used.

  Parts of the XP test suite do not pass on PHP7, as we still use other
  features incompatible with this new version. These are now in an extra
  unittest configuration file, *not-on-php-nightly.ini*. All other tests
  are expected to pass, and we're working on reducing the incompatible ones.
  (@thekid)

### Features

* Restored PHP7 forward compatibility by renaming internal *null* class
  to `__null`. See pull request xp-framework/core#86
  (@thekid)

### Bugfixes

* Fixed annotation parser to handle PHP7 eval() throwing exceptions instead
  of raising errors
  (@thekid)
* Fixed parity for PHP7 when handling undefined methods in PHP7, which
  now behaves like HHVM (!). See xp-framework/core#87
  (@thekid)
* Wrapped PHP7's `BaseException` inside a TargetInvocationException in
  method and constructor invocations
  (@thekid)

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

### Bugfixes

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
