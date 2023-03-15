<?php
declare(strict_types=1);

namespace LichtPHP\Container;

/**
 * Thrown by `Container` implementations when constructing an object failed because there is a cyclic dependency.
 *
 * @see Container::get()
 * @see Container::make()
 */
class CyclicDependencyException extends ConstructionException {
}
