<?php
declare(strict_types=1);

namespace LichtPHP\Container;

/**
 * Thrown by `ArrayContainer` and `Container` implementations when trying to put or register invalid entries.
 *
 * @see ArrayContainer::set()
 * @see Container::link()
 * @see Container::factory()
 */
class ContainerConfigurationException extends ContainerException {
}
