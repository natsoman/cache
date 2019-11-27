<?php

namespace Epignosis;

use Epignosis\Exceptions\InvalidKeyException;
use Psr\SimpleCache\{
    CacheInterface,
    InvalidArgumentException
};
use Epignosis\Interfaces\{
    KeyBuilderInterface,
    SerializerInterface,
    ClientInterface,
    CompressorInterface
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
     * @var CompressorInterface
     */
    protected $compressor;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param KeyBuilderInterface $keyBuilder
     * @param CompressorInterface $compressor
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer,
        KeyBuilderInterface $keyBuilder,
        CompressorInterface $compressor
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->keyBuilder = $keyBuilder;
        $this->compressor = $compressor;
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return mixed|null
     */
    public function get(string $key,callable $callback = null)
    {
        $cacheKey = $this->keyBuilder->build($key);
        $value = $this->getFromMemory($cacheKey);

        if ($value !== null) {
            return $value;
        }

        try {
            $value = $this->serializer->deserialize($this->compressor->decompress($this->cache->get($cacheKey)));
        } catch (InvalidArgumentException $e) {}

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
     * @return bool
     */
    public function set(string $key, $value, int $ttl = -1): bool
    {
        $status = false;

        try {
            $status = $this->cache->set(
                $cacheKey = $this->keyBuilder->build($key),
                $this->compressor->compress($this->serializer->serialize($value)),
                $ttl
            );

            if ($status === true) {
                $this->addToMemory($cacheKey, $value);
            }

        } catch (InvalidArgumentException $e) {}

        return $status;
    }

    /**
     * @param string|array $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $status = false;

        try {
            $status = $this->cache->delete($this->keyBuilder->build($key));
        } catch (InvalidArgumentException $e) {}

        return $status;
    }

    /**
     * @param array $keys
     * @return iterable
     * @throws InvalidKeyException
     */
    public function mGet(array $keys)
    {
        list($found, $notFound) = $this->searchKeys($keys);

        try {
            return array_merge($this->cache->getMultiple($notFound), $found);
        } catch (InvalidArgumentException $e) {
            throw new InvalidKeyException();
        }
    }

    /**
     * @param array $values
     * @param int $ttl
     * @return bool
     * @throws InvalidKeyException
     */
    public function mSet(array $values, $ttl = -1): bool
    {
        array_walk($values, function (&$v, &$k) {
            $k = $this->keyBuilder->build($k);
            $v = $this->compressor->compress($this->serializer->serialize($v));
        });

        try {
            $status = $this->cache->setMultiple($values, $ttl);
        } catch (InvalidArgumentException $e) {
            throw new InvalidKeyException();
        }

        if ($status) {
            $this->setToMemory($values);
        }

        return $status;
    }

    /**
     * @param array $keys
     * @return bool
     * @throws InvalidArgumentException
     */
    public function mDelete(array $keys): bool
    {
        $keys = array_map(function ($v) { return $this->keyBuilder->build($v); }, $keys);

        array_walk($keys,function ($v) {
            $this->deleteFromMemory($v);
        });

        return $this->cache->deleteMultiple($keys);
    }

    /**
     * @return KeyBuilderInterface
     */
    public function getKeyBuilder(): KeyBuilderInterface
    {
        return $this->keyBuilder;
    }
}