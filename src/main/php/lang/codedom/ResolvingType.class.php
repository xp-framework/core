<?php namespace lang\codedom;

use lang\XPClass;

trait ResolvingType {

  /**
   * Resolves a type in a given context. Recognizes classes imported via
   * the `use` statement.
   *
   * @param  string $type Type to resolve
   * @param  string $context Fully qualified class name
   * @param  [:string] $imports Map of local -> fully qualified class names
   * @return lang.XPClass
   */
  protected function typeOf($type, $context, $imports) {
    if ('self' === $type) {
      return XPClass::forName($context);
    } else if ('parent' === $type) {
      return XPClass::forName($context)->getParentclass();
    } else if (isset($imports[$type])) {
      return XPClass::forName($imports[$type]);
    } else if (strstr($type, '\\')) {
      return XPClass::forName(ltrim(strtr($type, '\\', '.'), '.'));
    } else if (class_exists($type, false) || interface_exists($type, false)) {
      return new XPClass($type);
    } else if (false !== ($p= strrpos($context, '.'))) {
      return XPClass::forName(substr($context, 0, $p + 1).$type);
    }
  }
}