<?php

namespace Natso;

use Natso\Serializer\NullSerializer;
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
    const NAMESPACE_SEPARATOR = ':';

    /**
     * @var MemoizationTrait
     */
    use MemoizationTrait;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var SerializerInterface|null
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
     * @var string
     */
    protected $namespace;

    /**
     * @var int Default Time To Live (seconds)
     */
    protected $ttl;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param KeyBuilderInterface|null $keyBuilder
     * @param CompressorInterface|null $compressor
     * @param array $options
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer = null,
        KeyBuilderInterface $keyBuilder = null,
        CompressorInterface $compressor = null,
        array $options = []
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer ?? new NullSerializer();
        $this->keyBuilder = $keyBuilder ?? new NullKeyBuilder();
        $this->compressor = $compressor ?? new NullCompressor();
        $this->namespace = $options['namespace'] ?? '';
        $this->ttl = $options['ttl'] ?? 3600;
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
                $v = $this->buildKey($v);
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
    public function setMultiple($values, $ttl = null): bool
    {
        $encodedValues = [];
        foreach ($values as $k => $v) {
            $encodedValues[$this->buildKey($k)] = $this->encode($v);
        }

        if (($status = $this->cache->setMultiple($encodedValues, $ttl ?? $this->ttl))) {
            $this->setToMemory((array)$values);
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys): bool
    {
        $cacheKeys = array_map(function ($v) {
            return $this->buildKey($v);
        }, (array)$keys);

        if ($status = $this->cache->deleteMultiple($cacheKeys)) {
            $this->unsetFromMemory((array)$keys);
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
     * @param string $key
     * @param mixed ...$args
     * @return string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function buildKey(string $key, ...$args): string
    {
        return sprintf('%s%s', $this->formatNamespace(), $this->keyBuilder->build($key, ...$args));
    }

    /**
     * Prepare value for caching
     *
     * @param mixed $value
     * @return string
     */
    protected function encode($value): string
    {
        return $this->compressor->compress($this->serializer->serialize($value));
    }

    /**
     * Restore cache value
     *
     * @param string|null $value
     * @return mixed
     */
    protected function decode(string $value)
    {
        return $this->serializer->deserialize($this->compressor->uncompress($value));
    }

    /**
     * @return string
     */
    protected function formatNamespace(): string
    {
        if ($this->namespace !== null) {
            return sprintf('%s%s', $this->namespace, static::NAMESPACE_SEPARATOR);
        }

        return '';
    }
}