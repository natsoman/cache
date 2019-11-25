<?php

declare(strict_types=1);

namespace Epignosis;

use Epignosis\Interfaces\KeyBuilderInterface;

class KeyBuilder implements KeyBuilderInterface {

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
     * @param $args
     * @return string|null
     */
    public function build(string $key, ...$args):? string
    {
        $cacheKey = $this->map($key);

        if (is_string($cacheKey)) {
            return $cacheKey;
        }
        
        if (is_callable($cacheKey)) {
            return $cacheKey(...$args);
        }
        
        throw new \InvalidArgumentException('Argument cannot be mapped to a cache key.');
    }

    /**
     * @param $key
     * @return string|callable
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