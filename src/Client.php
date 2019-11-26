<?php

namespace Epignosis;

use Psr\SimpleCache\{
    CacheInterface,
    InvalidArgumentException
};
use Epignosis\Interfaces\{
    KeyBuilderInterface,
    SerializerInterface,
    ClientInterface
};

class Client implements ClientInterface {

    use MemoizationTrait;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var KeyBuilderInterface
     */
    protected $keyBuilder;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param KeyBuilderInterface $keyBuilder
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer,
        KeyBuilderInterface $keyBuilder
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->keyBuilder = $keyBuilder;
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return mixed|null
     * @throws InvalidArgumentException
     */
    public function get(string $key,callable $callback = null)
    {
        $cacheKey = $this->keyBuilder->build($key);
        $value = $this->getFromMemory($cacheKey);

        if ($value !== null) {
            return $value;
        }

        $value = $this->cache->get($cacheKey);
        if ($value === null) {
            if (($value = $callback()) !== null) {
                $this->set($key, $value);
            }
        } else {
            $this->addToMemory($cacheKey, $value);
            return $value;
        }

        return $this->getFromMemory($cacheKey);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     * @throws InvalidArgumentException
     */
    public function set(string $key, $value, int $ttl = 3600): void
    {
        $this->cache->set($cacheKey = $this->keyBuilder->build($key), $this->serializer->serialize($value), $ttl);
        $this->addToMemory($cacheKey,$value);
    }

    /**
     * @param string|array $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->cache->delete($this->keyBuilder->build($key));
    }

    /**
     * @param array $keys
     * @return iterable
     * @throws InvalidArgumentException
     */
    public function mGet(array $keys)
    {
        return $this->cache->getMultiple($keys);
    }

    /**
     * @param array $values
     * @param int $ttl
     * @return bool
     * @throws InvalidArgumentException
     */
    public function mSet(array $values, $ttl = null)
    {
        return $this->cache->setMultiple($values, $ttl);
    }

    /**
     * @param array $keys
     * @return bool
     * @throws InvalidArgumentException
     */
    public function mDelete(array $keys)
    {
        return $this->cache->deleteMultiple(array_map(function ($v) { return $this->keyBuilder->build($v); }, $keys));
    }
}