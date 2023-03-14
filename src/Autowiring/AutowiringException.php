<?php
declare(strict_types=1);

namespace LichtPHP\Autowiring;

use Exception;

/**
 * Thrown by `Autowirer` when autowiring fails.
 *
 * @see Autowirer
 * @see Autowired
 */
class AutowiringException extends Exception {
}
