<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use LichtPHP\Autowiring\Autowired;
use Psr\Clock\ClockInterface;

class ClassWithReadonlyProperty {
    #[Autowired]
    public readonly ClockInterface $clock;
}
