<?php

namespace Natso\Adapter;

use Natso\Exception\CacheException;
use Psr\SimpleCache\CacheInterface;

class ApcAdapter implements CacheInterface {

    /**
     * @throws CacheException
     */
    public function __construct()
    {
        if (!extension_loaded('apc')) {
            throw new CacheException('Caching extension is missing');
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        return ($v = apc_fetch($key)) !== false ? $v : $default;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        return apc_store($key,$value, $ttl);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        return apc_delete($key);
    }

    /**
     * @inheritdoc
     */
    public function getMultiple($keys, $default = null)
    {
        return apc_fetch($keys);
    }

    /**
     * @inheritdoc
     */
    public function setMultiple($values,$ttl = null)
    {
        return apc_store($values);
    }

    /**
     * @inheritdoc
     */
    public function deleteMultiple($keys)
    {
        return (true === apc_delete($keys)) ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        return apc_clear_cache();
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return apc_exists($key);
    }
}