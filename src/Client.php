<?php
declare(strict_types=1);

namespace Epignosis;

use Epignosis\Interfaces\CacheInterface;
use Epignosis\Interfaces\KeyBuilderInterface;
use Epignosis\Interfaces\SerializerInterface;

class Client {

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
    public function setCache(CacheInterface $cache): void {
        $this->cache = $cache;
    }

    /**
     * @param string $key
     * @param callable $callback
     * @return iterable|null
     */
    public function get(string $key,callable $callback) {
        $value = $this->getFromMemory($key) ?? $this->serializer->deserialize($this->cache->get($this->keyBuilder->build($key)));

        if ($value === null) {
            if (($value = $callback()) !== null) {
                $value = $this->serializer->serialize($value);
                $this->cache->set($this->keyBuilder->build($key), $value);
            }
        }

        return $value;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $exp
     */
    public function set(string $key, $value, int $exp = 0): void {
        $this->cache->set(
            $this->keyBuilder->build($key),
            $this->serializer->serialize($value),
            $exp
        );
    }

    public function mGet(array $keys) {
        // TODO::implement
    }

    public function mSet(array $values) {
        // TODO::implement
    }

    public function ping(): string {
        return $this->cache->ping();
    }
}