<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use RuntimeException;
use Psr\SimpleCache\CacheException;

class SimpleCacheException extends RuntimeException implements CacheException {
}
