<?php

namespace Natso;

use Natso\Serializer\SerializerInterface;
use Natso\KeyBuilder\{
    KeyBuilderInterface,
    SimpleKeyBuilder
};
use Psr\SimpleCache\{
    CacheInterface
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
    )
    {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->keyBuilder = $keyBuilder ?? new SimpleKeyBuilder([]);
        $this->compressor = $compressor ?? new NullCompressor();
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        $cacheKey = $this->buildKey($key);
        $value = $this->getFromMemory($cacheKey);

        if ($value !== null) {
            return $value;
        }

        $cacheValue = $this->cache->get($cacheKey);
        if ($cacheValue !== null) {
            $value = $this->decode($cacheValue);
        }


        if ($value === null && is_callable($default)) {
            if (($value = $default()) !== null) {
                $this->set($key, $value);
            }
        } else {
            $this->addToMemory($cacheKey, $value);
        }

        return $this->getFromMemory($cacheKey);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $cacheKey = $this->buildKey($key);
        $value = $this->encode($value);

        if (($status = $this->cache->set($cacheKey, $value, $ttl)) === true) {
            $this->addToMemory($cacheKey, $value);
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        return $this->cache->delete($this->buildKey($key));
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
    public function getMultiple($keys, $default = null)
    {
        list($cached, $missedKey) = $this->searchKeys((array)$keys);

        if (count($missedKey) > 0) {
            array_walk($missedKey, function (&$v) {
                $this->buildKey($v);
            });

            $notFound = (array)$this->cache->getMultiple($missedKey);

            array_walk($notFound, function (&$value) use ($default) {
                if ($value !== null) {
                    $value = $this->decode($value);
                } elseif (is_callable($default)) {
                    $value = $default();
                } else {
                    $value = $default;
                }
            });

            return array_merge($cached, $notFound);
        }

        return $cached;
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

        $status = $this->cache->setMultiple($encodedValues, $ttl ?? 300);

        if ($status) {
            $this->setToMemory((array)$values);
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys): bool
    {
        $keys = array_map(function ($v) {
            return $this->buildKey($v);
        }, (array)$keys);

        $status = $this->cache->deleteMultiple($keys);

        if ($status) {
            array_walk($keys, function ($v) {
                $this->deleteFromMemory($v);
            });
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function has($key): bool
    {
        return $this->cache->has($key);
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