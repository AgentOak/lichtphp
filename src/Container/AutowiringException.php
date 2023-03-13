<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Exception;

/**
 * Thrown when autowiring fails.
 *
 * @see Autowirer
 * @see Autowired
 */
class AutowiringException extends Exception {
}
