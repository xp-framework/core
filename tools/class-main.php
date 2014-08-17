<?php

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  trigger_error('This version of the XP Framework requires PHP 5.4.0+, have PHP '.PHP_VERSION.PHP_EOL, E_USER_ERROR);
  exit(0x3d);
}

// {{{ internal void __fatal
function __fatal() {
  static $types= array(
    E_ERROR         => 'Fatal error',
    E_USER_ERROR    => 'Fatal error',
    E_PARSE         => 'Parse error',
    E_COMPILE_ERROR => 'Compile error'
  );

  $e= error_get_last();
  if (null !== $e && isset($types[$e['type']])) {
    __error($e['type'], $e['message'], $e['file'], $e['line']);
    create(new Error($types[$e['type']]))->printStackTrace();
  }
}
// }}}

// {{{ internal void __except(Exception e)
//     Exception handler
function __except($e) {
  fputs(STDERR, 'Uncaught exception: '.xp::stringOf($e));
  exit(0xff);
}
// }}}

// Verify SAPI
if ('cgi' === PHP_SAPI || 'cgi-fcgi' === PHP_SAPI) {
  ini_set('html_errors', 0);
  define('STDIN', fopen('php://stdin', 'rb'));
  define('STDOUT', fopen('php://stdout', 'wb'));
  define('STDERR', fopen('php://stderr', 'wb'));
} else if ('cli' !== PHP_SAPI) {
  trigger_error('[bootstrap] Cannot be run under '.PHP_SAPI.' SAPI', E_USER_ERROR);
  exit(0x3d);
}

if (!include(__DIR__.DIRECTORY_SEPARATOR.'lang.base.php')) {
  trigger_error('[bootstrap] Cannot determine boot class path', E_USER_ERROR);
  exit(0x3d);
}

$home= getenv('HOME');
list($use, $include)= explode(PATH_SEPARATOR.PATH_SEPARATOR, get_include_path());
bootstrap(array_merge(
  scanpath(explode(PATH_SEPARATOR, substr($use, 2).PATH_SEPARATOR.'.'), $home),
  explode(PATH_SEPARATOR, $include)
));

// Start I/O layers
$encoding= get_cfg_var('encoding');
iconv_set_encoding('internal_encoding', xp::ENCODING);
array_shift($_SERVER['argv']);
array_shift($argv);
if ($encoding) {
  foreach ($argv as $i => $val) {
    $_SERVER['argv'][$i]= $argv[$i]= iconv($encoding, xp::ENCODING, $val);
  }
}

ini_set('display_errors', 'false');
set_exception_handler('__except');
register_shutdown_function('__fatal');

try {
  exit(\lang\XPClass::forName($argv[0])->getMethod('main')->invoke(null, array(array_slice($argv, 1)))); 
} catch (\lang\SystemExit $e) {
  if ($message= $e->getMessage()) echo $message, "\n";
  exit($e->getCode());
}
