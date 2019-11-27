<?php

namespace Epignosis\Adapters;

use Epignosis\Exceptions\InvalidServiceException;
use RedisCluster;
use Redis as PhpRedis;
use Predis\Client as Predis;
use Psr\SimpleCache\CacheInterface;

class Redis implements CacheInterface {

    /**
     * @var PhpRedis|Predis|RedisCluster
     */
    protected $service;

    /**
     * @var PhpRedis|Predis|RedisCluster $service
     * @throws InvalidServiceException
     */
    public function __construct($service)
    {
        if ($service instanceof PhpRedis || $service instanceof Predis || $service instanceof RedisCluster) {
            $this->service = $service;
        } else {
            throw new InvalidServiceException('Service must be instance of Redis, RedisCluster or Predis');
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        return ($value = $this->service->get($key)) !== false ? $value : $default;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->service->setex($key, $ttl, $value);
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
     * @param array $keys
     * @return array
     */
    public function getMultiple($keys, $default = null): array
    {
        return array_map(
            function ($v) use ($default) { return $v === false ? $default : $v; },
            $this->service->mget($keys)
        );
    }

    /**
     * @inheritdoc
     * @param array $values
     */
    public function setMultiple($values,$ttl = null): bool
    {
        return $this->service->mset($values);
    }

    /**
     * @inheritdoc
     * @param array $keys
     */
    public function deleteMultiple($keys): bool
    {
        return $this->service->del($keys) === count($keys) ? true : false;
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
        return $this->service->exists($key) === 1 ? true : false;
    }
}