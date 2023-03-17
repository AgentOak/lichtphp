<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Clock;

use LichtPHP\Clock\SystemClock;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * @see SystemClock
 */
final class SystemClockTest extends TestCase {
    private ClockInterface $clock;

    protected function setUp(): void {
        $this->clock = new SystemClock();
    }

    public function testReturnsCurrentTime(): void {
        self::assertGreaterThanOrEqual(microtime(true), $this->clock->now()->format("U.u"));
    }

    public function testTimeIncreasesMonotonically(): void {
        $first = $this->clock->now();
        $second = $this->clock->now();
        usleep(2);
        $third = $this->clock->now();
        self::assertGreaterThanOrEqual($first->format("U.u"), $second->format("U.u"));
        self::assertGreaterThan($second->format("U.u"), $third->format("U.u"));
    }
}
