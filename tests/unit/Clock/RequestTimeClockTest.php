<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Clock;

use LichtPHP\Clock\RequestTimeClock;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

class RequestTimeClockTest extends TestCase {
    private ClockInterface $clock;

    protected function setUp(): void {
        $this->clock = new RequestTimeClock();
    }

    public function testReturnsRequestTime(): void {
        $this->assertEqualsWithDelta($_SERVER["REQUEST_TIME_FLOAT"], $this->clock->now()->format("U.u"), 0.001);
    }

    public function testTimeIsFrozen(): void {
        $first = $this->clock->now();
        usleep(2);
        $second = $this->clock->now();
        $this->assertEquals($first, $second);
    }
}
