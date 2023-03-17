<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use LichtPHP\Autowiring\Autowired;
use Psr\Clock\ClockInterface;

class ClassWithStaticProperty {
    #[Autowired]
    public static ClockInterface $clock;
}
