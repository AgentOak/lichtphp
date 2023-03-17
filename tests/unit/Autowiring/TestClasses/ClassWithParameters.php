<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use Psr\Clock\ClockInterface;

class ClassWithParameters {
    private ClockInterface $clock;

    public function __construct(ClockInterface $clock) {
        $this->clock = $clock;
    }

    public function getClock(): ClockInterface {
        return $this->clock;
    }
}
