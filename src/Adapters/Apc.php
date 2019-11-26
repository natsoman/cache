<?php

namespace Epignosis\Adapters;

use Psr\SimpleCache\CacheInterface;

class Apc implements CacheInterface {

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
        return apcu_store($key,$value, $ttl);
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
     * @param array $keys
     */
    public function getMultiple($keys, $default = null)
    {
        return apc_fetch($keys);
    }

    /**
     * @inheritdoc
     * @param array $values
     */
    public function setMultiple($values,$ttl = null)
    {
        return apcu_store($values);
    }

    /**
     * @inheritdoc
     * @param array $keys
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
        return apcu_clear_cache();
    }

    /**
     * @inheritdoc
     */
    public function has($key)
    {
        return apc_exists($key);
    }
}