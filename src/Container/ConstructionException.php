<?php
declare(strict_types=1);

namespace LichtPHP\Container;

/**
 * Thrown by `Container` implementations when constructing an object failed, e.g. wrapping `AutowiringException`s when
 * instantiating classes and calling callables.
 *
 * @see Container::get()
 * @see Container::make()
 * @see AutowiringException
 */
class ConstructionException extends ContainerException {
}
