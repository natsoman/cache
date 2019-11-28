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
     * @inheritdoc
     */
    public function get(string $key,callable $callback = null)
    {
        $cacheKey = $this->keyBuilder->build($key);
        $value = $this->getFromMemory($cacheKey);

        if ($value !== null) {
            return $value;
        }

        try {
            $value = $this->serializer->deserialize(
                $this->compressor->uncompress(
                    $this->cache->get($cacheKey)
                )
            );
        } catch (InvalidArgumentException $e) {}

        if ($value === null && is_string($callback)) {
            if (($value = $callback()) !== null) {
                $this->set($key, $value);
            }
        }

        if ($value !== null) {
            $this->addToMemory($cacheKey, $value);
            return $value;
        }

        return $this->getFromMemory($cacheKey);
    }

    /**
     * @inheritdoc
     */
    public function set(string $key, $value, int $ttl = 3600): bool
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function mGet(array $keys)
    {
        list($cached, $missedKey) = $this->searchKeys($keys);

        try {
            if (count($missedKey) > 0) {
                array_walk($missedKey, function (&$v) {
                    $this->keyBuilder->build($v);
                });

                $notFound = (array)$this->cache->getMultiple(array_values($missedKey), null);
                array_walk($notFound, function (&$value) {
                    $value = $this->serializer->deserialize($this->compressor->uncompress($value));
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
    public function mSet(array $values, $ttl = -1): bool
    {
        $encodedValues = $values;
        array_walk($encodedValues, function (&$v, &$k) {
            $k = $this->keyBuilder->build($k);
            $v = $this->compressor->compress($this->serializer->serialize($v));
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
    public function mDelete(array $keys): bool
    {
        $keys = array_map(function ($v) {
            return $this->keyBuilder->build($v);
        }, $keys);

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
    public function has(string $key): bool
	{
    	return $this->cache->has($key);
	}

    /**
     * @inheritdoc
     */
    public function getKeyBuilder(): KeyBuilderInterface
    {
        return $this->keyBuilder;
    }
}