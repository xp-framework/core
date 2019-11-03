<?php namespace net\xp_framework\unittest\reflection;

/** Used in MethodInvocationTest */
trait Database {

  public function connect($dsn) { return true; }

}