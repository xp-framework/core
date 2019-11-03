<?php namespace net\xp_framework\unittest\reflection;

/** Used in MethodInvocationTest and FieldAccessTest */
trait Database {
  private $conn= null;

  public function connect($dsn) {
    $this->conn= $dsn;
    return true;
  }
}