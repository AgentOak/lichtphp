<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

class ClassWithoutConstructor {
    public function getValue(): string {
        return "retval";
    }
}
