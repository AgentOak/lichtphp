<?php
declare(strict_types=1);

namespace LichtPHP\Error;

use RuntimeException;
use Throwable;

/**
 * Base class for user-facing errors, e.g. invalid inputs. Messages will be revealed even to unauthenticated users.
 */
class UserException extends RuntimeException {
    /**
     * @var int<400, 599> Default is 400 (Bad Request).
     */
    private const DEFAULT_HTTP_CODE = 400;

    /**
     * @var int<1, 254> Default is 1.
     */
    private const DEFAULT_EXIT_CODE = 1;

    /**
     * @param non-empty-string $message
     * @param int<400, 599> $httpCode HTTP response code when handling this exception in a web context.
     * @param int<1, 254> $exitCode Exit code when handling this exception in a CLI context.
     */
    public function __construct(
        string $message,
        private readonly int $httpCode = self::DEFAULT_HTTP_CODE,
        private readonly int $exitCode = self::DEFAULT_EXIT_CODE,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return int<400, 599>
     */
    final public function getHttpCode(): int {
        return $this->httpCode;
    }

    /**
     * @return int<1, 254>
     */
    final public function getExitCode(): int {
        return $this->exitCode;
    }
}
