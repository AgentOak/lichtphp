<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use LichtPHP\Autowiring\Autowired;
use Throwable;

class ClassWithThrowingMethod {
    private Throwable $e;

    public function __construct(Throwable $e) {
        $this->e = $e;
    }

    #[Autowired]
    public function doSomething(): void {
        throw $this->e;
    }
}
