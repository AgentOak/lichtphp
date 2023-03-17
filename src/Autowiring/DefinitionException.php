<?php
declare(strict_types=1);

namespace LichtPHP\Autowiring;

/**
 * Thrown by `Autowirer` when the class, method or object has an invalid definition that is not autowireable. This also
 * depends on the given arguments, e.g. an untyped parameter would normally be an invalid definition, but when the
 * parameter is given in the `$arguments` it would not need to be autowired.
 *
 * @see Autowirer
 */
class DefinitionException extends AutowiringException {
}
