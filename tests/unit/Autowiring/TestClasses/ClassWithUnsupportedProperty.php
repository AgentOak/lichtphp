<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use LichtPHP\Autowiring\Autowired;

class ClassWithUnsupportedProperty {
    #[Autowired]
    public ?int $level = 0;
}
