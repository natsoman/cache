<?php
declare(strict_types=1);

namespace Epignosis;

use Epignosis\Interfaces\CacheInterface;

class Cache implements CacheInterface {

    /**
     * @var null|\Redis|\RedisCluster
     */
    protected $cache;

    /**
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
     * // Connect a standalone server
     * new Cache(['host' => '127.0.0.1:6379']);
     *
     * // Connect on a cluster
     * new Cache(['host' => ['127.0.0.1:7000','127.0.0.1:7001']]);
     * </pre>
     */
    public function __construct($options) {
        if (extension_loaded('redis')) {
            $this->options = array_merge($options,$this->options);
            extract($this->options);
            if (isset($host) && is_array($host)) {
                try {
                    $this->cache = new \RedisCluster(null, $host);
                    // In the event we can't reach a master, and it has slaves, failover for read commands
                    $this->cache->setOption(\RedisCluster::OPT_SLAVE_FAILOVER, \RedisCluster::FAILOVER_ERROR);
                } catch (\RedisClusterException $e) {}
            } elseif (isset($host) && is_string($host)) {
                list($host,$port) = explode(':',$host);
                $this->cache = (new \Redis())->connect($host, $port, $timeout, $reserved, $retryInterval, $readTimeout);
            } else {
                throw new \InvalidArgumentException('Unknown redis host.');
            }
        } else {
            throw new \RuntimeException('redis extension is missing.');
        }
    }

    /**
     * @inheritdoc
     */
    public function get(string $key) {
        return $this->cache->get($key);
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, string $value, int $ttl = 0):? bool {
        return $this->cache->setex($key, $ttl, $value);
    }

    public function ping() {
        return $this->cache->ping(null);
    }
}