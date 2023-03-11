<?php
declare(strict_types=1);

namespace LichtPHP\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * Implementation of PSR-20: Clock returning the current real time clock as of the now() call.
 *
 * @see https://www.php-fig.org/psr/psr-20/
 * @see https://www.php-fig.org/psr/psr-20/meta/
 * @see ClockInterface
 */
final class SystemClock implements ClockInterface {
    public function now(): DateTimeImmutable {
        return new DateTimeImmutable();
    }
}
