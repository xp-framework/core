<?php namespace lang;

use lang\reflect\Modifiers;

/**
 * Generate generic runtime types.
 *
 * @test  xp://net.xp_framework.unittest.core.generics.GenericTypesTest
 */
class GenericTypes extends \lang\Object {

  /**
   * Creates a generic type
   *
   * @param   lang.XPClass base
   * @param   lang.Type[] arguments
   * @return  lang.XPClass created type
   */
  public function newType(XPClass $base, array $arguments) {
    return new XPClass(new \ReflectionClass($this->newType0($base, $arguments)));
  }

  /**
   * Creates a generic type
   *
   * @param   lang.XPClass base
   * @param   lang.Type[] arguments
   * @return  string created type's literal
   */
  public function newType0($base, $arguments) {

    // Verify
    $annotations= $base->getAnnotations();
    if (!isset($annotations['generic']['self'])) {
      throw new IllegalStateException('Class '.$base->name.' is not a generic definition');
    }
    $components= [];
    foreach (explode(',', $annotations['generic']['self']) as $cs => $name) {
      $components[]= ltrim($name);
    }
    $cs++;
    if ($cs !== sizeof($arguments)) {
      throw new IllegalArgumentException(sprintf(
        'Class %s expects %d component(s) <%s>, %d argument(s) given',
        $base->name,
        $cs,
        implode(', ', $components),
        sizeof($arguments)
      ));
    }
  
    // Compose names
    $cn= $qc= '';
    foreach ($arguments as $typearg) {
      $cn.= '¸'.strtr($typearg->literal(), '\\', '¦');
      $qc.= ','.$typearg->getName();
    }
    $name= $base->literal().'··'.substr($cn, 1);
    $qname= $base->name.'<'.substr($qc, 1).'>';

    // Create class if it doesn't exist yet
    if (!class_exists($name, false) && !interface_exists($name, false)) {
      $meta= \xp::$meta[$base->name];
      $meta['class'][DETAIL_GENERIC]= [$base->name, $arguments];

      // Parse placeholders into a lookup map
      $placeholders= [];
      foreach ($components as $i => $component) {
        $placeholders[$component]= $arguments[$i]->getName();
      }

      // Work on sourcecode
      $cl= $base->getClassLoader();
      if (!$cl || !($bytes= $cl->loadClassBytes($base->name))) {
        throw new IllegalStateException($base->name);
      }

      $codeunit= (new \lang\codedom\PhpSyntax())->parse($bytes);

      if (false !== ($ns= strrpos($name, '\\'))) {
        $decl= substr($name, $ns + 1);
        $namespace= substr($name, 0, $ns);
        $source= 'namespace '.$namespace.';';
      } else {
        $decl= $name;
        $namespace= null;
        $source= '';
      }

      $imports= [];
      foreach ($codeunit->imports() as $type) {
        $imports[substr($type, strrpos($type, '.')+ 1)]= $type;
        $source.= 'use '.strtr($type, '.', '\\').";\n";
      }

      $declaration= $codeunit->declaration();
      $generic= $this->genericAnnotation($declaration->annotations(), $base, $imports);
      if ($declaration->isInterface()) {
        $source.= $this->interfaceDeclaration($decl, $base, $declaration, $generic, $placeholders);
      } else if ($declaration->isClass()) {
        $source.= $this->classDeclaration($decl, $base, $declaration, $generic, $placeholders);
      } else {
        throw new IllegalStateException('Unsupported generic type '.$declaration->toString());
      }

      foreach ($declaration->body()->members() as $member) {
        if ($member->isMethod()) {
          $annotation= $this->genericAnnotation($member->annotations(), $base, $imports);

          $generic= [];
          if (isset($annotation['params'])) {
            foreach (explode(',', $annotation['params']) as $i => $placeholder) {
              if ('' === ($replaced= strtr(ltrim($placeholder), $placeholders))) {
                $generic[$i]= null;
              } else {
                $meta[1][$member->name()][DETAIL_ARGUMENTS][$i]= $replaced;
                $generic[$i]= $replaced;
              }
            }
          }
          if (isset($annotation['return'])) {
            $meta[1][$member->name()][DETAIL_RETURNS]= strtr($annotation['return'], $placeholders);
          }

          $source.= $this->methodDeclaration($member, $generic, $placeholders);
        } else if ($member->isField()) {
          $source.= $this->fieldDeclaration($member);
        } else if ($member->isConstant()) {
          $source.= $this->constantDeclaration($member);
        }
      }
      $source.= '}';

      // Create class
      // DEBUG fputs(STDERR, "@* ".substr($src, 0, strpos($src, '{'))." -> $qname\n");
      eval($source);
      if ($declaration->isClass()) {
        foreach ($components as $i => $component) {
          $name::$__generic[$component]= $arguments[$i];
        }
        method_exists($name, '__static') && call_user_func([$name, '__static']);
      }
      unset($meta['class'][DETAIL_ANNOTATIONS]['generic']);
      \xp::$meta[$qname]= $meta;
      \xp::$cn[$name]= $qname;

      // Create alias if no PHP namespace is present and a qualified name exists
      if (!$ns && strstr($qname, '.')) {
        class_alias($name, strtr($base->getName(), '.', '\\').'··'.substr($cn, 1));
      }
    }
    
    return $name;
  }

  private function genericAnnotation($annotations, $base, $imports) {
    if (null === $annotations) {
      return [];
    } else {
      $parsed= (new \lang\codedom\AnnotationSyntax())->parse($annotations)->resolve($base->getName(), $imports);
      return isset($parsed[null]['generic']) ? $parsed[null]['generic'] : [];
    }
  }

  private function typeArgs($annotation, $placeholders) {
    $args= [];
    foreach (explode(',', $annotation) as $j => $placeholder) {
      $args[]= Type::forName(strtr(ltrim($placeholder), $placeholders));
    }
    return $args;
  }

  /**
   * Returns class declaration
   */
  private function classDeclaration($name, $base, $declaration, $generic, $placeholders) {
    if (isset($generic['parent'])) {
      $parent= $this->newType0($base->getParentClass(), $this->typeArgs($generic['parent'], $placeholders));
    } else {
      $parent= $base->getParentClass()->literal();
    }

    $implements= [];
    $annotation= isset($generic['implements']) ? $generic['implements'] : [];
    foreach ($base->getDeclaredInterfaces() as $num => $interface) {
      if (isset($annotation[$num])) {
        $implements[]= $this->newType0($interface, $this->typeArgs($annotation[$num], $placeholders));
      } else {
        $implements[]= $interface->literal();
      }
    }

    if (Modifiers::isAbstract($declaration->modifiers())) {
      $modifiers= 'abstract ';
    } else {
      $modifiers= '';
    }

    return sprintf(
      "%sclass %s extends \\%s%s {\n  public static \$__generic= [];\n",
      $modifiers,
      $name,
      $parent,
      $implements ? ' implements \\'.implode(', \\', $implements) : ''
    );
  }

  /**
   * Returns interface declaration
   */
  private function interfaceDeclaration($name, $base, $declaration, $generic, $placeholders) {
    $extends= [];
    $annotation= isset($generic['extends']) ? $generic['extends'] : [];

    foreach ($base->getDeclaredInterfaces() as $num => $parent) {
      if (isset($annotation[$num])) {
        $extends[]= $this->newType0($parent, $this->typeArgs($annotation[$num], $placeholders));
      } else {
        $extends[]= $parent->literal();
      }
    }

    return sprintf(
      "interface %s%s{\n",
      $name,
      $extends ? ' extends \\'.implode(', \\', $extends) : ''
    );
  }

  /**
   * Returns method declaration
   */
  private function methodDeclaration($declaration, $generic, $placeholders) {
    $parameters= $declaration->parameters();

    $signature= '';
    foreach ($declaration->parameters() as $i => $param) {
      $signature.= sprintf(
        ', %s $%s%s',
        $param->restriction(),
        $param->name(),
        $param->hasDefault() ? '= '.$param->defaultValue() : ''
      );
    }

    $checks= '';
    foreach ($generic as $i => $type) {
      if (null === $type) {
        continue;
      } else if ('...' === substr($type, -3)) {
        $checks= $i ? '    $·args= array_slice(func_get_args(), '.$j.');' : '    $·args= func_get_args();';
        $checks.= sprintf(
          "    is('%s[]', \$·args) || raise('lang.IllegalArgumentException', 'Vararg #%d passed to %s must be of %1\$s, '.\xp::typeOf(\$·args).' given');\n",
          substr($type, 0, -3),
          $i + 1,
          $declaration->name() 
        );
      } else if (isset($parameters[$i])) {
        if ($parameters[$i]->hasDefault()) {
          $checks.= '    if ('.$parameters[$i]->defaultValue().' !== $'.$parameters[$i]->name().') ';
        } else {
          $checks.= '    ';
        }
        $checks.= sprintf(
          "is('%s', $%s) || raise('lang.IllegalArgumentException', 'Argument #%d passed to %s must be of %1\$s, '.\xp::typeOf(\$%2\$s).' given');\n",
          $type,
          $parameters[$i]->name(),
          $i + 1,
          $declaration->name() 
        );
      }
    }

    $tv= '';
    foreach ($placeholders as $key => $val) {
      $tv.= '    $'.$key."= \lang\Type::forName('".$val."');\n";
    }

    $body= $declaration->body();
    return sprintf(
      "  %s function %s(%s)%s\n",
      implode(' ', Modifiers::namesOf($declaration->modifiers())),
      $declaration->name(),
      substr($signature, 2),
      $body ? " {\n".$checks.$tv.'    '.$body."\n  }" : ';'
    );
  }

  /**
   * Returns field declaration
   */
  private function fieldDeclaration($declaration) {
    $initial= $declaration->initial();
    return sprintf(
      "  %s $%s%s;\n",
      implode(' ', Modifiers::namesOf($declaration->modifiers())),
      $declaration->name(),
      $initial ? '= '.$initial : ''
    );
  }

  /**
   * Returns constant declaration
   */
  private function constantDeclaration($declaration) {
    return sprintf(
      "  const %s = %s\n",
      $declaration->name(),
      $declaration->value()
    );
  }
}