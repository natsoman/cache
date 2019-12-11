<?php

namespace Natso;

use Natso\Serializer\SerializerInterface;
use Psr\SimpleCache\{
    CacheInterface
};
use Natso\KeyBuilder\{
    KeyBuilderInterface,
    NullKeyBuilder
};
use Natso\Compressor\{
    CompressorInterface,
    NullCompressor
};

class Cache implements CacheInterface
{
    /**
     * @var MemoizationTrait
     */
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
     * @var KeyBuilderInterface|null
     */
    protected $keyBuilder;

    /**
     * @var CompressorInterface|null
     */
    protected $compressor;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param KeyBuilderInterface|null $keyBuilder
     * @param CompressorInterface|null $compressor
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer,
        ?KeyBuilderInterface $keyBuilder,
        ?CompressorInterface $compressor
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->keyBuilder = $keyBuilder ?? new NullKeyBuilder();
        $this->compressor = $compressor ?? new NullCompressor();
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        $cacheKey = $this->buildKey($key);
        if (($value = $this->getFromMemory($cacheKey)) !== null) {
            return $value;
        }

        $cacheValue = $this->cache->get($cacheKey);
        if (is_string($cacheValue)) {
            $this->addToMemory($cacheKey, $this->decode($cacheValue));
        } else {
            return $default;
        }

        return $this->getFromMemory($cacheKey);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $cacheKey = $this->buildKey($key);
        if (($status = $this->cache->set($cacheKey, $this->encode($value), $ttl)) === true) {
            $this->addToMemory($cacheKey, $value);
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        list($hits, $misses) = $this->searchKeys((array)$keys);
        if (count($misses) > 0) {
            array_walk($misses, function (&$v) {
                $this->buildKey($v);
            });

            $cacheHits = (array)$this->cache->getMultiple($misses);
            array_walk($cacheHits, function (&$value) use ($default) {
                if ($value !== null) {
                    $value = $this->decode($value);
                } else {
                    $value = $default;
                }
            });

            $hits = array_merge($hits, $cacheHits);
        }

        return $hits;
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $encodedValues = (array)$values;
        array_walk($encodedValues, function (&$v, &$k) {
            $k = $this->buildKey($k);
            $v = $this->encode($v);
        });

        if (($status = $this->cache->setMultiple($encodedValues, $ttl ?? 300))) {
            $this->setToMemory((array)$values);
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys): bool
    {
        $keys = array_map(function ($v) { return $this->buildKey($v); }, (array)$keys);
        if ($status = $this->cache->deleteMultiple($keys)) {
            $this->unsetFromMemory($keys);
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $cacheKey = $this->buildKey($key);
        if ($status = $this->cache->delete($cacheKey)) {
            $this->deleteFromMemory($cacheKey);
        }

        return $status;
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->cache->clear();
    }

    /**
     * @inheritdoc
     */
    public function has($key): bool
    {
        return $this->cache->has($this->buildKey($key));
    }

    /**
     * @inheritdoc
     */
    public function buildKey($key, ...$args): string
    {
        return $this->keyBuilder->build($key, ...$args);
    }

    /**
     * Prepare value for caching
     *
     * @param mixed $value
     * @return string
     */
    protected function encode($value): string
    {
        return $this->compressor->compress(
            $this->serializer->serialize($value)
        );
    }

    /**
     * Restore cache value
     *
     * @param string|null $value
     * @return mixed
     */
    protected function decode(string $value)
    {
        return $this->serializer->deserialize(
            $this->compressor->uncompress($value)
        );
    }
}