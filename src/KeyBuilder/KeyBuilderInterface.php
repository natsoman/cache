<?php

namespace Natso\KeyBuilder;

use Psr\SimpleCache\InvalidArgumentException;

interface KeyBuilderInterface
{
    /**
     * Transform the given key to cache key
     *
     * @param string $key
     * @param mixed  ...$args Extra arguments.
     *
     * @throws InvalidArgumentException
     *  MUST be thrown if the $key string is not string or callback which returns string.
     *
     * @return string The key that should be cached.
     */
    public function build(string $key, ...$args);
}