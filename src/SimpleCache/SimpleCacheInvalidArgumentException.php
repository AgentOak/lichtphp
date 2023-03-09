<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use Psr\SimpleCache\InvalidArgumentException;

class SimpleCacheInvalidArgumentException extends SimpleCacheException implements InvalidArgumentException {
}
