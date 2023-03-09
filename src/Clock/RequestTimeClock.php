<?php
declare(strict_types=1);

namespace LichtPHP\Clock;

use DateTimeImmutable;
use Exception;
use LichtPHP\Util;
use Psr\Clock\ClockInterface;

/**
 * Implementation of PSR-20: Clock based on $_SERVER request time. For CLI this contains the script start time.
 * Note that this always returns the same time on each now() call and cannot be used to measure durations during the
 * scripts execution.
 */
final class RequestTimeClock implements ClockInterface {
    private readonly DateTimeImmutable $requestTime;

    /**
     * @throws Exception When creating DateTimeImmutable object failed
     */
    public function __construct() {
        $this->requestTime = Util::ensure(
            DateTimeImmutable::createFromFormat("U.u", (string) $_SERVER["REQUEST_TIME_FLOAT"])
        );
    }

    public function now(): DateTimeImmutable {
        return $this->requestTime;
    }
}
