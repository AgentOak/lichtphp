<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use Psr\Clock\ClockInterface;

class ClassWithPromotedParameters {
    public function __construct(private readonly ClockInterface $clock) {
    }

    public function getClock(): ClockInterface {
        return $this->clock;
    }
}
