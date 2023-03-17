<?php
declare(strict_types=1);

namespace LichtPHP\Clock;

use DateTimeImmutable;
use LichtPHP\Util;
use RuntimeException;

/**
 * Implementation of PSR-20: Clock based on `$_SERVER` request time. For CLI this contains the script start time.
 *
 * This implementation is not suitable for long-running CLI applications. For example, the TTL cache invalidation would
 * not work for some `Cache` implementations.
 *
 * @see FrozenClock
 * @see Cache
 */
final class RequestTimeClock extends FrozenClock {
    /**
     * @throws RuntimeException When request time is unavailable or could not be parsed
     */
    public function __construct() {
        if (!array_key_exists("REQUEST_TIME_FLOAT", $_SERVER)) {
            throw new RuntimeException("Request time unavailable");
        }

        parent::__construct(Util::ensure(
            DateTimeImmutable::createFromFormat("U.u", (string) $_SERVER["REQUEST_TIME_FLOAT"])
        ));
    }
}
