<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Thrown by `Cache` implementations when a method is called with an invalid key.
 *
 * @see InvalidArgumentException
 * @see CacheInterface
 * @see Cache
 */
class SimpleCacheInvalidArgumentException extends SimpleCacheException implements InvalidArgumentException {
}
