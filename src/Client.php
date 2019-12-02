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
        KeyBuilderInterface $keyBuilder = null,
        CompressorInterface $compressor = null
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
        $cacheKey = $this->buildKey($key);
        $value = $this->getFromMemory($cacheKey);

        if ($value !== null) {
            return $value;
        }

        try {
            $value = $this->decode($this->cache->get($cacheKey));
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
            $status = $this->cache->set($cacheKey = $this->buildKey($key), $this->encode($value), $ttl);
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
            $status = $this->cache->delete($this->buildKey($key));
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
                    $this->buildKey($v);
                });

                $notFound = (array)$this->cache->getMultiple(array_values($missedKey), null);
                array_walk($notFound, function (&$value) {
                    $value = $this->decode($value);
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
    public function mDelete(array $keys): bool
    {
        $keys = array_map(function ($v) {
            return $this->buildKey($v);
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
    public function buildKey($key,...$args): string
	{
		if ($this->keyBuilder !== null){
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
	protected function decode(?string $value)
	{
		$value = $this->serializer->deserialize($value);

		if ($this->compressor !== null) {
			return $this->compressor->uncompress($value);
		}

		return $value;
	}
}