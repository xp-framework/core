XP Framework Runner Entry points
================================

These files serve as entry point for the PHP engine and take care of bootstrapping the framework. Most of the APIs serve an internal purpose only; the public APIs exported by  these files are the following:

Syntax support
--------------
`with`, `raise`, `ensure`, `cast`, `delete`, `newinstance`, `create`, `typeof`.

PHP error handling
------------------
`xp::errorAt()`, `xp::gc()`

Extension methods
-----------------
`xp::extensions($class, $scope)`, `new \import('class.Name');`

Misc
----
`xp::stringOf()`, `xp::null()`.
