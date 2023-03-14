<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use DateInterval;
use Psr\SimpleCache\CacheInterface;

/**
 * Implementation of PSR-16: Simple cache and `Cache`, that only validates keys as required by PSR-16, but does not
 * store any elements. `get()` will always return the default, `has()` will always return `false`, `delete()`, `set()`
 * and `clear()` are no-ops that return successfully.
 *
 * @see CacheInterface
 * @see Cache
 */
final class DummyCache extends AbstractCache {
    public function clear(): bool {
        return true;
    }

    public function get(string $key, mixed $default = null): mixed {
        self::validateKey($key);
        return $default;
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool {
        self::validateKey($key);
        return true;
    }

    public function delete(string $key): bool {
        self::validateKey($key);
        return true;
    }

    public function has(string $key): bool {
        self::validateKey($key);
        return false;
    }
}
