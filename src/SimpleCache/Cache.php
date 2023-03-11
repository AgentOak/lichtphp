<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Extends PSR-16: Simple cache with convenient methods that accept callables.
 *
 * @see https://www.php-fig.org/psr/psr-16/
 * @see https://www.php-fig.org/psr/psr-16/meta/
 * @see CacheInterface
 */
interface Cache extends CacheInterface {
    /**
     * Like get(), but calls a given callable to produce a default value if and only if the key is absent from the
     * cache.
     *
     * @param callable(): mixed $producer
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     * @see CacheInterface::get()
     */
    public function getOrCompute(string $key, callable $producer): mixed;

    /**
     * Like get(), but calls a given callable to produce a value if and only if the key is absent from the cache.
     * The result of the callable is then stored in the cache with the given $ttl. This operation is not atomic.
     *
     * @param callable(): mixed $producer
     * @throws InvalidArgumentException MUST be thrown if the $key string is not a legal value.
     * @see CacheInterface::get()
     * @see CacheInterface::set()
     */
    public function getOrCache(string $key, callable $producer, DateInterval|int|null $ttl): mixed;
}
