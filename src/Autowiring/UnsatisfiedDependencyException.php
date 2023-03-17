<?php
declare(strict_types=1);

namespace LichtPHP\Autowiring;

/**
 * Thrown by `Autowirer` when a dependency is required, but not available in the given `ContainerInterface` or if
 * obtaining a dependency (including optionals) from `ContainerInterface::get()` threw a `ContainerExceptionInterface`.
 *
 * @see Autowirer
 * @see Autowirer::__construct
 * @see ContainerInterface
 * @see ContainerInterface::get()
 * @see ContainerExceptionInterface
 */
class UnsatisfiedDependencyException extends AutowiringException {
}
