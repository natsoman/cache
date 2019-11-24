<?php

declare(strict_types=1);

namespace Epignosis;

use Epignosis\Interfaces\CacheInterface;
use Epignosis\Interfaces\KeyBuilderInterface;
use Epignosis\Interfaces\SerializerInterface;

class Client {

    const TTL = 3600;

    use MemoizationTrait;

    /**
     * @var \Epignosis\Interfaces\CacheInterface
     */
    protected $cache;

    /**
     * @var \Epignosis\Interfaces\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Epignosis\Interfaces\KeyBuilderInterface
     */
    protected $keyBuilder;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface|null $serializer
     * @param KeyBuilderInterface|null $keyBuilder
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
     * It allows you to change cache connection on runtime
     *
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return mixed|null
     */
    public function get(string $key,callable $callback)
    {
        $cacheKey = $this->keyBuilder->build($key);
        $value = $this->getFromMemory($cacheKey);

        if ($value !== null) {
            return $value;
        }

        $value = $this->cache->get($key);
        if ($value === null) {
            if (($value = $callback()) !== null) {
                $this->set($key, $value,static::TTL);
            }
        }

        return $this->getFromMemory($key);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $ttl
     */
    public function set(string $key, $value, int $ttl = 0): void 
    {
        $this->cache->set(
            $cacheKey = $this->keyBuilder->build($key),
            $this->serializer->serialize($value),
            $ttl
        );
        
        $this->addToMemory($cacheKey,$value);
    }

    /**
     * @param $key
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->cache->delete(
            $this->keyBuilder->build($key)
        );
    }
    
    public function mGet(array $keys) 
    {
        // TODO::implement
    }

    public function mSet(array $values) 
    {
        // TODO::implement
    }
}