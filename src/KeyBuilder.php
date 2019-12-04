<?php

namespace Epignosis;

use Epignosis\Exceptions\InvalidKeyException;
use Epignosis\Interfaces\KeyBuilderInterface;

class KeyBuilder implements KeyBuilderInterface
{

    /**
     * @var array
     */
    protected $map;

    /**
     * @inheritdoc
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $key
     * @param mixed $args
     * @return string
     * @throws InvalidKeyException
     */
    public function build(string $key, ...$args): string
    {
        $cacheKey = $this->map($key);

        if (is_string($cacheKey)) {
            return $cacheKey;
        }

        if (is_callable($cacheKey)) {
            return $cacheKey(...$args);
        }

        throw new InvalidKeyException('Key cannot be mapped to a cache key.');
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function map($key)
    {
        return isset($this->map[$key]) ? $this->map[$key] : $key;
    }

    /**
     * @return array
     */
    protected function getMap(): array
    {
        return $this->map;
    }
}