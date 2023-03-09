<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use DateInterval;
use LichtPHP\Clock\RealClock;
use LichtPHP\Util;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Base class for implementations of PSR-16: Simple cache.
 */
abstract class AbstractCache implements Cache {
    // TODO: Add support for layering?
    // TODO: Add support for partitioning?
    // TODO: Add APCu cache implementation
    // TODO: Add PDO/SQL cache implementation?
    // TODO: Add file cache implementation?
    /**
     * @var literal-string RegEx of must-support set of keys as per PSR-16.
     */
    protected const KEY_REGEX = "/[a-zA-Z\d_.]{1,64}/";

    protected static function validateKey(string $key): void {
        if (Util::ensure(preg_match(static::KEY_REGEX, $key)) === 0) {
            throw new SimpleCacheInvalidArgumentException("Given key has invalid format");
        }
    }

    protected static function intervalToSeconds(DateInterval $interval): int {
        $now = (new RealClock())->now(); // TODO: Get from DI container?
        return $now->add($interval)->getTimestamp() - $now->getTimestamp();
    }

    /**
     * @throws InvalidArgumentException
     */
    final protected static function validateIterable(mixed $iterable): void {
        if (!is_iterable($iterable)) {
            throw new SimpleCacheInvalidArgumentException("Given keys is not an iterable");
        }
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        self::validateIterable($keys);

        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool {
        self::validateIterable($values);

        // Not necessary but saves DateTimeImmutable construction for every individual set()
        if ($ttl instanceof DateInterval) {
            $ttl = static::intervalToSeconds($ttl);
        }

        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function deleteMultiple(iterable $keys): bool {
        self::validateIterable($keys);

        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function getOrCompute(string $key, callable $producer): mixed {
        return $this->get($key) ?? $producer();
    }

    public function getOrCache(string $key, callable $producer, DateInterval|int|null $ttl): mixed {
        $value = $this->get($key);
        if ($value === null) {
            $value = $producer();
            $this->set($key, $value, $ttl);
        }
        return $value;
    }
}
