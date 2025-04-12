<?php namespace lang;

/**
 * Indicates a class format error - for example:
 * 
 * - Class file does not declare any classes
 * - Class file does not declare class by file name
 *
 * @see   lang.ClassLoader#loadClass
 * @test  net.xp_framework.unittest.reflection.ClassLoaderTest
 */
class ClassFormatException extends XPException implements ClassLoadingException {
  
}
