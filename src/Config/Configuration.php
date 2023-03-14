<?php
declare(strict_types=1);

namespace LichtPHP\Config;

use LogicException;

/**
 * Used to load immutable, user-supplied configuration.
 *
 * The basic structure for configurations is a key-value store with non-empty string keys and a recursive section
 * hierarchy. Implementations may not support sections or subsections to an arbitrary depth. Furthermore,
 * implementations may not support all data types or have limitations on option and section names. In such cases,
 * `LogicExceptions` are thrown on unsupported method calls.
 */
interface Configuration {
    // TODO: Support arrays
    // TODO: Specify case sensitivity
    // TODO: Environment variable handling
    // TODO: Configuration chain? - Separate Exception for not found key?
    /**
     * Returns another `Configuration` representing a subsection of this Configuration specified by the given section
     * `$name`.
     *
     * @param literal-string $name Name of the (sub)section to obtain
     * @throws LogicException If no (deeper) sections are supported by this Configuration or the given section name
     *                        is invalid
     * @throws ConfigurationException if no section with the given `$name` is available
     */
    public function section(string $name): Configuration;

    /**
     * Returns a string value from this configuration section specified by the given `$option` name.
     * If the configured value is not of type string or the underlying configuration format is untyped,
     * implementations may choose to convert scalars to a string as long as no data loss occurs.
     *
     * @template TAllowBlank of bool
     * @param literal-string $option
     * @param TAllowBlank $allowBlank
     * @return (TAllowBlank is true ? string : non-empty-string)
     * @throws LogicException If this Configuration does not support strings or the given `$option` name is invalid
     * @throws ConfigurationException If no option with the given name is available in this section or the
     *                                value specified by the user is not representable as a string, or the value is
     *                                a blank string whereas caller did not `$allowBlank`
     */
    public function getString(string $option, bool $allowBlank = false): string;

    /**
     * Returns a boolean value from this configuration section specified by the given `$option` name.
     * If the configured value is not of type boolean or the underlying configuration format is untyped,
     * implementations may choose to map select scalar values to a boolean.
     *
     * @param literal-string $option
     * @throws LogicException If this Configuration does not support booleans or the given `$option` name is invalid
     * @throws ConfigurationException If no option with the given name is available in this section or the
     *                                value specified by the user is not representable as a boolean
     */
    public function getBool(string $option): bool;

    /**
     * Returns an integer value from this configuration section specified by the given `$option` name.
     * If the configured value is not of type integer or the underlying configuration format is untyped,
     * implementations may choose to convert scalars to an integer as long as no data loss occurs.
     * The integer value can be limited to a valid range.
     *
     * @template TMin of int
     * @template TMax of int
     * @param literal-string $option
     * @param TMin $min
     * @param TMax $max
     * @return (TMin is int<0, max> ? (TMin is positive-int ? positive-int : int<0, max>)
     *         : (TMax is int<min, 0> ? (TMax is negative-int ? negative-int : int<min, 0>) : int))
     * @throws LogicException If this Configuration does not support integers or the given `$option` name is invalid
     * @throws ConfigurationException If no option with the given name is available in this section or the
     *                                value specified by the user is not representable as an integer, or the value is
     *                                outside of the given range
     */
    public function getInt(string $option, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int;

    /**
     * Returns a float value from this configuration section specified by the given `$option` name.
     * If the configured value is not of type float or the underlying configuration format is untyped, implementations
     * may choose to convert scalars to a float as long as no data loss occurs.
     *
     * @param literal-string $option
     * @throws LogicException If this Configuration does not support floats or the given `$option` name is invalid
     * @throws ConfigurationException If no option with the given name is available in this section or the
     *                                value specified by the user is not representable as a float
     */
    public function getFloat(string $option): float;
}
