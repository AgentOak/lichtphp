<?php
declare(strict_types=1);

namespace LichtPHP\Tests\Autowiring\TestClasses;

use LichtPHP\Autowiring\Autowired;

class ClassWithUntypedProperty {
    #[Autowired]
    public $value = null;
}
