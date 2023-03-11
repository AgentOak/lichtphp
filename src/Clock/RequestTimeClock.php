<?php
declare(strict_types=1);

namespace LichtPHP\Clock;

use DateTimeImmutable;
use LichtPHP\Util;
use RuntimeException;

/**
 * Implementation of PSR-20: Clock based on $_SERVER request time. For CLI this contains the script start time.
 *
 * @see FrozenClock
 */
final class RequestTimeClock extends FrozenClock {
    /**
     * @throws RuntimeException When creating DateTimeImmutable object failed
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
