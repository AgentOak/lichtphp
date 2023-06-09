<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Clock;

use DateTimeImmutable;
use LichtPHP\Clock\FrozenClock;
use PHPUnit\Framework\TestCase;

/**
 * @see FrozenClock
 */
class FrozenClockTest extends TestCase {
    public function testReturnsGivenTime(): void {
        $timestamp = 123456.123456;
        $clock = new FrozenClock(new DateTimeImmutable("@$timestamp"));
        self::assertEqualsWithDelta($timestamp, $clock->now()->format("U.u"), 0.001);
    }

    public function testTimeIsFrozen(): void {
        $clock = new FrozenClock(new DateTimeImmutable());
        $first = $clock->now();
        usleep(2);
        $second = $clock->now();
        self::assertEquals($first, $second);
    }
}
