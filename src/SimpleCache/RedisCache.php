<?php
declare(strict_types=1);

namespace LichtPHP\SimpleCache;

use DateInterval;
use LichtPHP\Util;
use Psr\Clock\ClockInterface;
use Psr\SimpleCache\CacheException;
use Redis;
use RedisException;
use RuntimeException;

/**
 * Implementation of PSR-16: Simple cache and Cache, using a Redis server as backend. Requires phpredis extension.
 *
 * @see CacheInterface
 * @see Cache
 */
final class RedisCache extends AbstractCache {
    /**
     * @var non-empty-string
     */
    private const IGBINARY_TEST_FUNCTION = "igbinary_serialize";

    private readonly Redis $redis;

    /**
     * @param string $prefix Prefix for all cache keys.
     * @throws CacheException If connection failed
     * @throws RuntimeException If Redis extension is not available
     * @see Redis::pconnect()
     * @see Redis::OPT_PREFIX
     * @see AbstractCache::__construct()
     */
    public function __construct(
        ClockInterface $clock,
        private readonly string $host,
        private readonly int $port,
        private readonly float $timeout = 0.0,
        private readonly int $dbindex = 0,
        private readonly string $prefix = ""
    ) {
        if (!class_exists(Redis::class)) {
            throw new RuntimeException("PHP extension Redis not loaded");
        }

        parent::__construct($clock);

        // TODO: Update to phpredis 6.0.0 constructor initialization
        $this->redis = new Redis();
        $this->connect();
    }

    /**
     * @throws CacheException
     */
    private function connect(): void {
        // TODO: Reconnect for long-running processes?
        try {
            Util::ensure($this->redis->pconnect($this->host, $this->port, $this->timeout));
            Util::ensure($this->redis->select($this->dbindex));
            if (function_exists(self::IGBINARY_TEST_FUNCTION)) {
                Util::ensure($this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY));
            }
            if ($this->prefix !== "") {
                Util::ensure($this->redis->setOption(Redis::OPT_PREFIX, $this->prefix));
            }
        } catch (RedisException $e) {
            throw new SimpleCacheException("Error connecting to Redis cache", previous: $e);
        }
    }

    public function get(string $key, mixed $default = null): mixed {
        self::validateKey($key);

        try {
            $value = $this->redis->get($key);
        } catch (RedisException $e) {
            throw new SimpleCacheException("Error when getting from Redis cache", previous: $e);
        }
        return $value === false ? $default : $value;
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool {
        self::validateKey($key);

        if ($ttl instanceof DateInterval) {
            $ttl = $this->intervalToSeconds($ttl);
        }

        if (is_int($ttl) && $ttl <= 0) {
            return $this->delete($key);
        }

        try {
            if ($ttl === null) {
                return $this->redis->set($key, $value) === true;
            } else {
                return $this->redis->setex($key, $ttl, $value) === true;
            }
        } catch (RedisException $e) {
            throw new SimpleCacheException("Error when setting in Redis cache", previous: $e);
        }
    }

    public function delete(string $key): bool {
        self::validateKey($key);

        try {
            return $this->redis->del($key) <= 1;
        } catch (RedisException $e) {
            throw new SimpleCacheException("Error when deleting from Redis cache", previous: $e);
        }
    }

    public function clear(): bool {
        // TODO: Does not respect $prefix option!
        try {
            return $this->redis->flushDB() === true;
        } catch (RedisException $e) {
            throw new SimpleCacheException("Error when clearing Redis cache", previous: $e);
        }
    }

    public function has(string $key): bool {
        self::validateKey($key);

        try {
            return $this->redis->exists($key) === 1;
        } catch (RedisException $e) {
            throw new SimpleCacheException("Error when existence-checking Redis cache", previous: $e);
        }
    }
}
