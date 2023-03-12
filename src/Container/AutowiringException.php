<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use LogicException;

/**
 * Thrown when autowiring fails.
 *
 * @see Autowirer
 * @see Autowired
 */
class AutowiringException extends LogicException {
}
