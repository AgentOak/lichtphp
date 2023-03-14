<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Thrown when `ContainerInterface::get()` and `Container::make()` are called with an unknown ID.
 *
 * @see NotFoundExceptionInterface
 * @see ContainerInterface::get()
 * @see Container::make()
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface {
}
