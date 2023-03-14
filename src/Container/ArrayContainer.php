<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use ArrayAccess;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Extends PSR-11: Container Interface with ability to put objects into the Container. Objects that have been stored
 * using `set()` can be retrieved with `get()`. The container automatically contains itself, under the
 * `Psr\Container\ContainerInterface` ID, this interfaces fully-qualified name and its own fully-qualified class name.
 *
 * This container support IDs that specify a non-built-in type, i.e. a class or interface (including those predefined
 * in the PHP library). The validity of IDs is only checked when adding elements into the container to be compliant with
 * PSR-11. Naturally, it follows that for invalid IDs, `has()` can only ever return `false`, and `get()` can only throw
 * a `NotFoundExceptionInterface`.
 *
 * For consistency reasons, once stored, objects cannot be removed or overwritten.
 *
 * `ArrayAccess` is implemented to allow accessing the container like an array with string keys.
 * `ArrayAccess::offsetExists()` maps to `ContainerInterface::has()`, `ArrayAccess::offsetGet()` to
 * `ContainerInterface::get()`, `ArrayAccess::offsetSet()` to `ContainerInterface::set()`, and
 * `ArrayAccess::offsetUnset()` is not supported and throws an exception.
 *
 * @see https://www.php-fig.org/psr/psr-11/
 * @see https://www.php-fig.org/psr/psr-11/meta/
 * @see ContainerInterface
 * @see NotFoundExceptionInterface
 * @extends ArrayAccess<class-string, object>
 */
interface ArrayContainer extends ContainerInterface, ArrayAccess {
    // TODO: Sealing, i.e. require seal() before get(), then no more put/register? Use Builder for Container?
    // TODO: Support non-class IDs as per PSR-11?
    // TODO: Support non-object values?
    // TODO: Method to list/debug entries
    /**
     * Stores an object in the container. Overwriting previous entries is not allowed; i.e. a second `set()` call for
     * the same id will throw an exception.
     *
     * @template T of object
     * @param class-string<T> $id A fully-qualified non-built-in type name, i.e. the name of a class or interface
     * @param T $implementation An object whose type extends or implements the type specified by the `$id`
     * @throws ContainerExceptionInterface If `$id` is not a non-built-in type or an entry for this `$id` already exists
     */
    public function set(string $id, object $implementation): void;

    /**
     * Not supported, always throws a `LogicException`.
     *
     * @throws LogicException
     */
    public function offsetUnset(mixed $offset): void;
}
