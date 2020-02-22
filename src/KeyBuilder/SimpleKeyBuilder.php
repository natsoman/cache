<?php

namespace Natso\KeyBuilder;

use Natso\Exception\InvalidKeyException;

class SimpleKeyBuilder implements KeyBuilderInterface
{
    /**
     * @var array
     */
    protected $map;

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @param string $key
     * @param mixed [] $args
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

        throw new InvalidKeyException('Key must be a string or a callback which returns string');
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function map($key)
    {
        return isset($this->map[$key]) ? $this->map[$key] : $key;
    }
}