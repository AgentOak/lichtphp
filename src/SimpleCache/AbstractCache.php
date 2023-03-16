<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use DateInterval;
use LichtPHP\Util;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Base class for implementations of PSR-16: Simple cache and `Cache`.
 *
 * @see CacheInterface
 * @see Cache
 */
abstract class AbstractCache implements Cache {
    // TODO: Add support for layering?
    // TODO: Add support for partitioning?
    // TODO: Add APCu cache implementation
    // TODO: Add memory cache implementation (glorified memoization?)
    // TODO: Add PDO/SQL cache implementation?
    // TODO: Add file cache implementation?
    /**
     * @var literal-string RegEx of must-support set of keys as per PSR-16.
     */
    protected const KEY_REGEX = "/[a-zA-Z\d_.]{1,64}/";

    /**
     * @param ClockInterface $clock Clock to obtain a reference time from when converting `DateInterval`s to seconds
     */
    public function __construct(protected readonly ClockInterface $clock) {
    }

    protected static function validateKey(string $key): void {
        if (Util::ensure(preg_match(static::KEY_REGEX, $key)) === 0) {
            throw new SimpleCacheInvalidArgumentException("Given key has invalid format");
        }
    }

    protected function intervalToSeconds(DateInterval $interval): int {
        /*
         * DateIntervals cannot be directly converted into seconds, because they store years, months, days, hours,
         * minutes and seconds separately. However, not every month has the same number of days (also leap years and
         * leap seconds exist). To know the specific amount of time an interval represents, you need a point in time
         * as reference. Our best bet is to use the current time as starting point, therefore we need access to a clock.
         */
        $now = $this->clock->now();
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
            $ttl = $this->intervalToSeconds($ttl);
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
