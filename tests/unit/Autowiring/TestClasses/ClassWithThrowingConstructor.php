<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use Throwable;

class ClassWithThrowingConstructor {
    public function __construct(Throwable $e) {
        throw $e;
    }
}
