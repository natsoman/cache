<?php
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
     * @return mixed
     */
    public function set(string $key, string $value, int $ttl = 0);
}