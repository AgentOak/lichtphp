<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use LichtPHP\Autowiring\Autowired;
use RuntimeException;

class ClassWithStaticMethod {
    #[Autowired]
    private static function configure(): void {
        throw new RuntimeException("This code may not run");
    }
}
