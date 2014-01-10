<?php

define('EPREPEND_IDENTIFIER', "\356\277\277");
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
  trigger_error('This version of the XP Framework requires PHP 5.3.0+, have PHP '.PHP_VERSION.PHP_EOL, E_USER_ERROR);
  exit(0x3d);
}

// {{{ internal string __output(string buf)
//     Output handler. Checks for fatal errors
function __output($buf) {
  if (false === ($s= strpos($buf, EPREPEND_IDENTIFIER))) return $buf;
  $l= strlen(EPREPEND_IDENTIFIER);
  $e= strpos($buf, EPREPEND_IDENTIFIER, $s + $l);
  $message= trim(substr($buf, $s + $l, $e - $l));
  fputs(STDOUT, substr($buf, 0, $s));
  fputs(STDERR, create(new Error($message))->toString());
  fputs(STDOUT, substr($buf, $e + $l));
  return '';
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
} else if (defined('HHVM_VERSION')) {
  var_dump($argv);
  var_dump(strtr(file_get_contents('/proc/'.getmypid().'/cmdline'), "\0", ' '));
  ini_set('date.timezone', 'Europe/Berlin');
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

ini_set('error_prepend_string', EPREPEND_IDENTIFIER);
ini_set('error_append_string', EPREPEND_IDENTIFIER);
set_exception_handler('__except');
ob_start('__output');

try {
  exit(\lang\XPClass::forName($argv[0])->getMethod('main')->invoke(null, array(array_slice($argv, 1)))); 
} catch (\lang\SystemExit $e) {
  if ($message= $e->getMessage()) echo $message, "\n";
  exit($e->getCode());
}
