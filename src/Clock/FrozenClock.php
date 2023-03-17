<?php
declare(strict_types=1);

namespace LichtPHP\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * Implementation of PSR-20: Clock returning a specific, fixed `DateTimeImmutable`. Note that this always returns the
 * same time on each `now()` call and cannot be used to measure durations during the scripts execution.
 *
 * This implementation is not suitable for long-running CLI applications. For example, the TTL cache invalidation would
 * not work for some `Cache` implementations.
 *
 * @see https://www.php-fig.org/psr/psr-20/
 * @see https://www.php-fig.org/psr/psr-20/meta/
 * @see ClockInterface
 * @see Cache
 */
class FrozenClock implements ClockInterface {
    public function __construct(private readonly DateTimeImmutable $now) {
    }

    public function now(): DateTimeImmutable {
        return $this->now;
    }
}
