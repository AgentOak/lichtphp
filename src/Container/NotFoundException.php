<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Thrown when ContainerInterface::get() is called with an unknown ID.
 *
 * @see ContainerInterface::get()
 */
class NotFoundException extends ContainerException implements NotFoundExceptionInterface {
}
