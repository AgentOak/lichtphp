<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use ArrayAccess;
use InvalidArgumentException;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Minimal implementation of PSR-11 Container Interface that stores and retrieves objects by an ID. Only objects that
 * have been stored explicitly using put() can be retrieved with get(). This container only support IDs that specify a
 * non-built-in type, i.e. a class, interface or trait (including those predefined in the PHP library).
 *
 * The container automatically contains itself, under the "Psr\Container\ContainerInterface" ID and its own
 * fully-qualified class name.
 *
 * For consistency reasons, once stored, objects cannot be removed or overwritten.
 *
 * @see StaticContainer::put()
 * @implements ArrayAccess<class-string, object>
 */
class StaticContainer implements ContainerInterface, ArrayAccess {
    /**
     * @var array<class-string, object>
     */
    protected array $entries = [];

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct() {
        $this->put(ContainerInterface::class, $this);
        $this->put(self::class, $this);
    }

    /**
     * @throws ContainerException
     */
    protected static function checkSupportedId(string $id): void {
        // TODO: Support non-class IDs as per PSR-11?
        if (!(class_exists($id) || interface_exists($id) || trait_exists($id))) {
            throw new ContainerException("Non-type id '$id' is not supported");
        }
    }

    public function get(string $id): mixed {
        if (!$this->has($id)) {
            throw new NotFoundException("Missing implementation of '$id'");
        }

        return $this->entries[$id];
    }

    public function has(string $id): bool {
        return array_key_exists($id, $this->entries);
    }

    /**
     * Stores an object in the container. Overwriting previous entries is not allowed; i.e. a second put() call for the
     * same id will throw an exception.
     *
     * @template T of object
     * @param class-string<T> $id A fully-qualified non-built-in type name, i.e. the name of a class, interface or trait
     * @param T $implementation An object whose type extends, implements or uses the type specified by the id
     * @throws ContainerExceptionInterface If $id is not a non-built-in type or an entry for this $id already exists
     */
    public function put(string $id, object $implementation): void {
        static::checkSupportedId($id);

        // Can not use has() here because it might be overridden to include additional object sources
        if (array_key_exists($id, $this->entries)) {
            throw new ContainerException("Duplicate implementation for '$id' not supported");
        } elseif (!($implementation instanceof $id)) {
            throw new ContainerException("Implementation for '$id' does not implement this type");
        }

        $this->entries[$id] = $implementation;
    }

    public function offsetExists(mixed $offset): bool {
        return is_string($offset) && $this->has($offset);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function offsetGet(mixed $offset): mixed {
        if (!is_string($offset)) {
            throw new InvalidArgumentException("Offset must be a string");
        }

        return $this->get($offset);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function offsetSet(mixed $offset, mixed $value): void {
        if (!is_string($offset) || !is_object($value)) {
            throw new InvalidArgumentException("Offset must be a string and value must be an object");
        }

        $this->put($offset, $value);
    }

    public function offsetUnset(mixed $offset): void {
        throw new LogicException("Unsetting is not supported");
    }
}
