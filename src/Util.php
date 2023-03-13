<?php
declare(strict_types=1);

namespace LichtPHP;

use ReflectionClass;
use RuntimeException;

/**
 * Provides convenient utility methods.
 */
final class Util {
    private function __construct() {
    }

    /**
     * Throws an exception if the given parameter is false, otherwise returns it as-is. Can be used to check return
     * values of functions that return a specific value on error instead of throwing an exception.
     *
     * @param mixed $condition The value to check.
     * @param mixed $error The value that signals an error, false by default.
     * @throws RuntimeException If the given value is the error value.
     */
    public static function ensure(mixed $condition, mixed $error = false): mixed {
        if ($condition === $error) {
            throw new RuntimeException("An unknown error occured.");
        }

        return $condition;
    }

    /**
     * Returns whether the given `$className` denotes an instantiable class, that is, is a class (not an interface), not
     * abstract and has a public constructor.
     *
     * @param string $className A fully-qualified class name
     */
    public static function isInstantiableClass(string $className): bool {
        return class_exists($className) && (new ReflectionClass($className))->isInstantiable();
    }

    /**
     * Returns whether the given `$className` denotes a valid class type, i.e. a class or interface with the given name
     * exists.
     *
     * @param string $className A fully-qualified type name
     */
    public static function isClassType(string $className): bool {
        return class_exists($className) || interface_exists($className);
    }
}
