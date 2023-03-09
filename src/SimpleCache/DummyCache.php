<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use DateInterval;

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
