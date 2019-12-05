<?php

namespace Natso\Adapters;

use RedisCluster;
use Redis as PhpRedis;
use Predis\Client as Predis;
use Psr\SimpleCache\CacheInterface;
use Natso\Exceptions\CacheException;

class Redis implements CacheInterface
{

    /**
     * @var PhpRedis|Predis|RedisCluster
     */
    protected $service;

    /**
     * @throws CacheException
     * @var PhpRedis|Predis|RedisCluster $service
     */
    public function __construct($service)
    {
        if ($service instanceof PhpRedis || $service instanceof Predis || $service instanceof RedisCluster) {
            $this->service = $service;
        } else {
            throw new CacheException('Service must be instance of Redis, RedisCluster or Predis');
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null): ?string
    {
        return ($value = $this->service->get($key)) !== false ? $value : $default;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->service->setex($key, $ttl ?? 3600, $value);
    }

    /**
     * @inheritdoc
     */
    public function delete($key): bool
    {
        return $this->service->del($key) === 1 ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null): array
    {
        $cacheRecords = $this->service->mget((array)$keys);

        foreach ($cacheRecords as &$value) {
            if ($value === false) {
                $value = $default;
            }
        }

        return array_combine((array)$keys, $cacheRecords);
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return $this->service->mset((array)$values);
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys): bool
    {
        return $this->service->del($keys) === count((array)$keys) ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function clear(): bool
    {
        return $this->service->flushAll();
    }

    /**
     * @inheritdoc
     */
    public function has($key): bool
    {
        return $this->service->exists($key);
    }
}