<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use InvalidArgumentException;
use LichtPHP\Util;
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
        $this->set(ContainerInterface::class, $this);
        $this->set(ArrayContainer::class, $this);
        $this->set(self::class, $this);
    }

    public function get(string $id): object {
        return $this->entries[$id] ?? throw new NotFoundException("No implementation for ID '$id'");
    }

    public function has(string $id): bool {
        return array_key_exists($id, $this->entries);
    }

    public function set(string $id, object $implementation): void {
        // Can not use has() here because it might be overridden to include additional object sources
        if (array_key_exists($id, $this->entries)) {
            throw new ContainerException("Duplicate implementation for ID '$id' not supported");
        } elseif (!Util::isClassType($id)) {
            throw new ContainerException("ID '$id' is not a valid class type");
        } elseif (!($implementation instanceof $id)) {
            throw new ContainerException("Object of type '{$implementation::class}' is not an instance of ID '$id'");
        }

        $this->entries[$id] = $implementation;
    }

    public function offsetExists(mixed $offset): bool {
        return is_string($offset) && $this->has($offset);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function offsetGet(mixed $offset): object {
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

        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void {
        throw new LogicException("Unsetting is not supported");
    }
}
