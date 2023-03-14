<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Psr\SimpleCache\CacheException;

/**
 * Thrown by `Cache` implementations for cache failures.
 *
 * @see CacheException
 * @see CacheInterface
 * @see Cache
 */
class SimpleCacheException extends RuntimeException implements CacheException {
}
