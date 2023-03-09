<?php
declare(strict_types=1);

namespace LichtPHP\Container;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends ContainerException implements NotFoundExceptionInterface {
}
