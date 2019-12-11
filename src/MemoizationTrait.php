<?php

namespace Natso;

trait MemoizationTrait {

    /**
     * @var array
     */
    protected $memory = [];
    
    /**
     * @param string $key
     * @param $value
     */
    protected function addToMemory(string $key, $value): void 
    {
    	$this->memory[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    protected function getFromMemory(string $key)
    {
    	return $this->memory[$key] ?? null;
    }

    /**
     * @param string $key
     */
    protected function deleteFromMemory(string $key): void 
    {
        unset($this->memory[$key]);
    }

    /**
     * @param array $values Associative array
     */
    protected function setToMemory(array $values): void 
    {
    	$this->memory = array_merge($this->memory, $values);
    }

    /**
     * @param array $keys
     */
    protected function unsetFromMemory(array $keys): void
    {
        $this->memory = array_diff_key($this->memory, array_flip($keys));
    }

    /**
     * @param array $keys []
     * @return array
     */
    protected function searchKeys(array $keys): array
    {
        $hits = $misses = [];
        foreach($keys as $key) {
            if (isset($this->memory[$key])) {
                $hits[$key] = $this->memory[$key];
            } else {
                $misses[] = $key;
            }
        }

        return [$hits,$misses];
    }

    protected function cleanMemory(): void
    {
        $this->memory = [];
    }
}