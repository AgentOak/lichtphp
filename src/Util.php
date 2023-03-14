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
     * @template T of mixed
     * @param T $condition The value to check
     * @param mixed $error The value that signals an error, false by default
     * @return T
     * @throws RuntimeException If the given value is the error value
     */
    public static function ensure(mixed $condition, mixed $error = false): mixed {
        if ($condition === $error) {
            throw new RuntimeException("An unknown error occured.");
        }

        return $condition;
    }

    /**
     * Memoize a given `$callable`, i.e. cache its result and return the cached results on successive calls with the
     * same argument. The cache is stored in memory.
     *
     * @template P of array-key
     * @template T of mixed
     * @param callable(P): T $callable A deterministic callable, i.e. always returns the same for the same argument
     * @return callable(P): T The callable with memoization
     */
    public static function memoize(callable $callable): callable {
        return static function (mixed $arg) use ($callable) {
            static $cache = [];

            if (array_key_exists($arg, $cache)) {
                return $cache[$arg];
            }

            $result = $callable($arg);
            $cache[$arg] = $result;
            return $result;
        };
    }

    /**
     * @var array<string, bool>
     */
    private static array $instantiabilityCache = [];

    /**
     * Returns whether the given `$className` denotes an instantiable class, that is, is a class (not an interface), not
     * abstract and has a public constructor.
     *
     * Note that this will trigger the autoloader, which may affect performance. Therefore this check should be avoided
     * unless the class is about to be used anyway.
     *
     * @param string $className A fully-qualified class name
     * @phpstan-assert-if-true class-string $className
     */
    public static function isInstantiableClass(string $className): bool {
        if (array_key_exists($className, self::$instantiabilityCache)) {
            return self::$instantiabilityCache[$className];
        }

        $result = class_exists($className) && (new ReflectionClass($className))->isInstantiable();
        self::$instantiabilityCache[$className] = $result;
        return $result;
    }

    /**
     * Returns whether the given `$className` denotes a valid class type, i.e. a class or interface with the given name
     * exists.
     *
     * Note that this will trigger the autoloader, which may affect performance. Therefore this check should be avoided
     * unless the type is about to be used anyway.
     *
     * @param string $className A fully-qualified type name
     * @phpstan-assert-if-true class-string $className
     */
    public static function isClassType(string $className): bool {
        return class_exists($className) || interface_exists($className);
    }
}
