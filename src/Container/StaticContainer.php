<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use InvalidArgumentException;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Minimal implementation of PSR-11: Container Interface and ArrayContainer that stores and retrieves objects by a
 * non-built-in type ID.
 *
 * @see ContainerInterface
 * @see ArrayContainer
 */
class StaticContainer implements ArrayContainer {
    /**
     * @var array<class-string, object>
     */
    protected array $entries = [];

    /**
     * @throws ContainerExceptionInterface
     */
    public function __construct() {
        $this->put(ContainerInterface::class, $this);
        $this->put(ArrayContainer::class, $this);
        $this->put(self::class, $this);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected static function checkSupportedId(string $id): void {
        if (!(class_exists($id) || interface_exists($id))) {
            throw new ContainerException("Non-class-type id '$id' is not supported");
        }
    }

    public function get(string $id): mixed {
        return $this->entries[$id] ?? throw new NotFoundException("No implementation for '$id'");
    }

    public function has(string $id): bool {
        return array_key_exists($id, $this->entries);
    }

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
