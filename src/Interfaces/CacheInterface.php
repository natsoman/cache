<?php

declare(strict_types=1);

namespace Epignosis\Interfaces;

interface CacheInterface {

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param string $value
     * @param int $ttl
     * @return bool
     */
    public function set(string $key, string $value, int $ttl = 0): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;
}