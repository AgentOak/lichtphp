<?php
declare(strict_types=1);

namespace LichtPHP\Config;

use RuntimeException;

/**
 * Thrown to indicate the user has supplied an invalid configuration. Including, but not limited to:
 *
 * - Configuration not loadable (e.g. syntax error)
 * - Missing required section
 * - Missing required option
 * - Supplied option value has incompatible type that cannot be converted into the required type
 * - Supplied option value is invalid for some reason
 */
class ConfigurationException extends RuntimeException {
}
