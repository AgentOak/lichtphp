<?php
declare(strict_types=1);

namespace LichtPHP;

use RuntimeException;

class Util {
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
}
