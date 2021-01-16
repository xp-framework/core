XP Framework Core
=================
[![Build status on GitHub](https://github.com/xp-framework/core/workflows/Tests/badge.svg)](https://github.com/xp-framework/core/actions)
[![Build status on TravisCI](https://secure.travis-ci.org/xp-framework/core.png)](http://travis-ci.org/xp-framework/core)
[![Build status on AppVeyor](https://ci.appveyor.com/api/projects/status/bb9gkkq1o7f6m2ns?svg=true)](https://ci.appveyor.com/project/thekid/core)
[![BSD License](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENSE.md)
[![Requires PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.svg)](http://php.net/)
[![Supports PHP 8.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-8_0plus.svg)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/core/version.png)](https://packagist.org/packages/xp-framework/core)

This is the XP Framework's development checkout. 

Installation
------------
If you just want to use the XP Framework, grab a release using `composer require xp-framework/core`. If you wish to use this development checkout, clone this repository instead.

### Runners
The entry point for software written in the XP Framework is not the PHP
interpreter's CLI / web server API but either a command line runner or
a specialized *web* entry point. These runners can be installed by using
the following one-liner:

```sh
$ cd ~/bin
$ curl -sSL https://bintray.com/artifact/download/xp-runners/generic/setup-8.3.0.sh | sh
```

### Using it
To use the the XP Framework development checkout, put the following in your `~/bin/xp.ini` file:

```ini
use=/path/to/xp/core
```

Finally, start `xp -v` to see it working:

```sh
$ xp -v
XP 10.5.3-dev { PHP/8.0.0 & Zend/4.0.0 } @ Windows NT SURFACE 10.0 build 19042 (Windows 10) AMD64
Copyright (c) 2001-2021 the XP group
FileSystemCL<./src/main/php>
FileSystemCL<./src/test/php>
FileSystemCL<./src/main/resources>
FileSystemCL<./src/test/resources>
FileSystemCL<.>
```

Basic usage
-----------
The XP Framework runs scripts or classes.

### Hello World
Save the following sourcecode to a file called `ageindays.script.php`:

```php
<?php namespace examples;

use util\{Date, Dates};
use util\cmd\Console;

$span= Dates::diff(new Date($argv[1]), Date::now());
Console::writeLine('Hey, you are ', $span->getDays(), ' days old');
```

Now run it:

```sh
$ xp ageindays.script.php 1977-12-14
Hey, you are 15452 days old
```

Alternatively, you can put this code inside a class and give it a static *main* method. This way, you can use features like inheritance, trait inclusion etcetera. This time, save the code to a file called `AgeInDays.class.php`.

```php
<?php

use util\{Date, Dates};
use util\cmd\Console;

class AgeInDays {

  public static function main(array $args): int {
    $span= Dates::diff(new Date($args[0]), Date::now());
    Console::writeLine('Hey, you are ', $span->getDays(), ' days old');
    return 0;
  }
}
```

*Note the arguments have shifted by one: If you want the class' name, simply use `self::class`!*

```sh
$ xp AgeInDays 1977-12-14
Hey, you are 15452 days old
```

Contributing
------------
To contribute, use the GitHub way - fork, hack, and submit a pull request! :octocat:

**Enjoy!**
