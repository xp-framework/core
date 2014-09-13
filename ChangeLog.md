XP Framework Core ChangeLog
========================================================================

## 6.0.0 / ????-??-??

### Heads up!

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
