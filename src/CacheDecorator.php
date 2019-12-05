<?php

namespace Natso;

use Natso\Exceptions\InvalidKeyException;
use Psr\SimpleCache\{
    CacheInterface,
    InvalidArgumentException
};
use Natso\Interfaces\{
    KeyBuilderInterface,
    SerializerInterface,
    CompressorInterface
};

class CacheDecorator implements CacheInterface
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
        $this->keyBuilder = $keyBuilder;
        $this->compressor = $compressor;
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

        try {
            $cacheValue = $this->cache->get($cacheKey);
            if ($cacheValue !== null) {
                $value = $this->decode($cacheValue);
            }
        } catch (InvalidArgumentException $e) {
        }

        if ($value === null && is_callable($default)) {
            if (($value = $default()) !== null) {
                $this->set($key, $value);
            }
        } elseif ($value === null) {
            $this->set($key, $default);
        }

        return $this->getFromMemory($cacheKey);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        $status = false;
        try {
            $cacheKey = $this->buildKey($key);
            $value = $this->encode($value);
            $status = $this->cache->set($cacheKey, $value, $ttl);
            if ($status === true) {
                $this->addToMemory($cacheKey, $value);
            }
        } catch (InvalidArgumentException $e) {
        }

        return $status;
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        $status = false;

        try {
            $status = $this->cache->delete($this->buildKey($key));
        } catch (InvalidArgumentException $e) {
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
    public function getMultiple($keys, $callback = null)
    {
        list($cached, $missedKey) = $this->searchKeys($keys);

        try {
            if (count($missedKey) > 0) {
                array_walk($missedKey, function (&$v) {
                    $this->buildKey($v);
                });

                $notFound = (array)$this->cache->getMultiple(array_values($missedKey), null);
                array_walk($notFound, function (&$value) {
                    if (is_string($value)) {
                        $value = $this->decode($value);
                    }
                });

                return array_merge($cached, $notFound);
            }
        } catch (InvalidArgumentException $e) {
            throw new InvalidKeyException();
        }

        return $cached;
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values, $ttl = null)
    {
        $encodedValues = $values;
        array_walk($encodedValues, function (&$v, &$k) {
            $k = $this->buildKey($k);
            $v = $this->encode($v);
        });

        try {
            $status = $this->cache->setMultiple($encodedValues, $ttl);
        } catch (InvalidArgumentException $e) {
            throw new InvalidKeyException();
        }

        if ($status) {
            $this->setToMemory($values);
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

        try {
            $status = $this->cache->deleteMultiple($keys);
        } catch (InvalidArgumentException $e) {
            throw new InvalidKeyException();
        }

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
        if ($this->keyBuilder !== null) {
            return $this->keyBuilder->build($key, ...$args);
        }

        return $key;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function encode($value): string
    {
        $value = $this->serializer->serialize($value);

        if ($this->compressor !== null) {
            return $this->compressor->compress($value);
        }

        return $value;
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    protected function decode(string $value)
    {
        if ($this->compressor !== null) {
            $value = $this->compressor->uncompress($value);
        }

        $value = $this->serializer->deserialize($value);

        return $value;
    }
}