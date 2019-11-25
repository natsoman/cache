<?php

declare(strict_types=1);

namespace Epignosis;

use \Epignosis\Interfaces\CacheInterface;

class RedisCache implements CacheInterface {

    /**
     * @var \Redis|\RedisCluster
     */
    protected $service;

    /**
     * Default options
     *
     * @var array
     */
    protected $options = [
        'timeout' => 3.0,
        'readTimeout' => 3.0,
        'reserved' => null,
        'persistent' => false,
        'retryInterval' => 0
    ];

    /**
     * @var array $options
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @example
     * <pre>
     * // Connect on a standalone server
     * new RedisCache(['host' => '127.0.0.1:6379']);
     *
     * // Connect on a cluster
     * new RedisCache(['host' => ['127.0.0.1:7000','127.0.0.1:7001']]);
     * </pre>
     */
    public function __construct($options)
    {
        $this->options = array_merge($options, $this->options);
        extract($this->options);
        if (isset($host) && is_array($host)) {
            try {
                $this->service = new \RedisCluster(null, $host, $timeout, $readTimeout, $persistent);
                // In the service we can't reach a master, and it has slaves, failover for read commands
                $this->service->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, \RedisCluster::FAILOVER_ERROR);
            } catch (\RedisClusterException $e) {
                throw new \RuntimeException('Redis cluster connection failed.');
            }
        } elseif (isset($host) && is_string($host)) {
            list($host, $port) = explode(':', $host);
            $this->service = new \Redis();
            $this->service->connect($host, $port ?? null, $timeout, $reserved, $retryInterval, $readTimeout);
        } else {
            throw new \InvalidArgumentException('Unknown redis host.');
        }
    }

    /**
     * @inheritdoc
     */
    public function get(string $key)
    {
        return $this->service->get($key);
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, string $value, int $ttl = 3600): bool
    {
        return $this->service->setex($key, $ttl, $value);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $key): bool
    {
        return $this->service->del($key) === 1 ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function multiGet(array $keys): array
    {
        return $this->service->mget($keys);
    }

    /**
     * @inheritdoc
     */
    public function multiSet(array $values): bool
    {
        return $this->service->mset($values);
    }

    /**
     * @inheritdoc
     */
    public function multiDelete(array $keys): bool
    {
        return $this->service->del($keys)  === count($keys) ? true : false;
    }
}