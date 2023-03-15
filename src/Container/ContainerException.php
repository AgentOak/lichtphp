<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * Base class for exceptions thrown by `ArrayContainer` and `Container` implementations in case of failures.
 *
 * @see ContainerExceptionInterface
 * @see ArrayContainer
 * @see Container
 * @see AutowiringException
 */
abstract class ContainerException extends Exception implements ContainerExceptionInterface {
}
