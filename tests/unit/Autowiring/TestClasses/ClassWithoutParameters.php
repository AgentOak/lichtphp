<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use LichtPHP\Autowiring\Autowired;
use Psr\Clock\ClockInterface;

class ClassWithoutParameters {
    private int $numCalls = 0;
    #[Autowired]
    public ?ClockInterface $clock = null;
    private ?ClockInterface $secondClock = null;
    private int $methodCalls = 0;

    // Intentionally annotated with useless attribute
    #[Autowired]
    public function __construct() {
        $this->numCalls++;
    }

    public function getConstructorCalls(): int {
        return $this->numCalls;
    }

    #[Autowired]
    public function setSecondClock(ClockInterface $clock): void {
        $this->secondClock = $clock;
    }

    public function getSecondClock(): ?ClockInterface {
        return $this->secondClock;
    }

    #[Autowired]
    public function configure(): void {
        $this->methodCalls++;
    }

    public function getMethodCalls(): int {
        return $this->methodCalls;
    }
}
