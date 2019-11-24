<?php

declare(strict_types=1);

namespace Epignosis;

use Epignosis\Interfaces\KeyBuilderInterface;

class KeyBuilder implements KeyBuilderInterface {

    const NAMESPACE_SEPERATOR = ':';

    /**
     * @var array 
     */
    protected $map = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @inheritdoc
     */
    public function __construct($namespace, array $map)
    {
        $this->applyNamespace($namespace);
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
     * @return callable|string
     */
    protected function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param callable|array $namespace
     */
    protected function applyNamespace($namespace): void
    {
        if (is_callable($namespace)) {
            $namespace = $namespace();
        }
        
        if (is_array($namespace)) {
            $namespace = implode(static::NAMESPACE_SEPERATOR,$namespace);
        }
        
        $this->namespace = sprintf('%s:', $namespace);
    }

    /**
     * @return array
     */
    protected function getMap(): array
    {
        return $this->map;
    }
}