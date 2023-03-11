<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use ArrayAccess;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Extends PSR-11: Container Interface with ability to put objects into the Container. Objects that have been stored
 * using put() can be retrieved with get(). The container automatically contains itself, under the
 * "Psr\Container\ContainerInterface" ID, this interfaces fully-qualified name and its own fully-qualified class name.
 *
 * This container support IDs that specify a non-built-in type, i.e. a class, interface or trait (including those
 * predefined in the PHP library).
 *
 * For consistency reasons, once stored, objects cannot be removed or overwritten.
 *
 * ArrayAccess is implemented to allow accessing the container like an array with string keys.
 * ArrayAccess::offsetExists() maps to ContainerInterface::has(), ArrayAccess::offsetGet() to ContainerInterface::get(),
 * ArrayAccess::offsetSet() to ContainerInterface::put(), and ArrayAccess::offsetUnset() is not supported and throws an
 * Exception.
 *
 * @see https://www.php-fig.org/psr/psr-11/
 * @see https://www.php-fig.org/psr/psr-11/meta/
 * @implements ArrayAccess<class-string, object>
 */
interface ArrayContainer extends ContainerInterface, ArrayAccess {
    // TODO: Support sealing, i.e. no more IDs may be registered/put after seal()? Require seal() before get()?
    // TODO: Support non-class IDs as per PSR-11?
    /**
     * Stores an object in the container. Overwriting previous entries is not allowed; i.e. a second put() call for the
     * same id will throw an exception.
     *
     * @template T of object
     * @param class-string<T> $id A fully-qualified non-built-in type name, i.e. the name of a class, interface or trait
     * @param T $implementation An object whose type extends, implements or uses the type specified by the id
     * @throws ContainerExceptionInterface If $id is not a non-built-in type or an entry for this $id already exists
     */
    public function put(string $id, object $implementation): void;

    /**
     * Not supported, always throws a LogicException.
     *
     * @throws LogicException
     */
    public function offsetUnset(mixed $offset): void;
}
