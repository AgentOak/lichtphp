<?php
declare(strict_types=1);

namespace LichtPHP\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * Implementation of PSR-20: Clock returning the current real time clock as of the now() call.
 */
final class RealClock implements ClockInterface {
    public function now(): DateTimeImmutable {
        return new DateTimeImmutable();
    }
}
