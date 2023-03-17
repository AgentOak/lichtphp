<?php
declare(strict_types=1);

namespace LichtPHP\Autowiring;

/**
 * Thrown by `Autowirer` to wrap `Throwable`s that are thrown within a called method (including constructors).
 *
 * @see Autowirer
 */
class InvocationException extends AutowiringException {
}
