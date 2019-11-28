<?php

namespace Epignosis;

trait MemoizationTrait {

    /**
     * @var array
     */
    protected $memory = [];

    /**
     * @var bool
     */
    protected $use = true;

    /**
     * @param string $key
     * @param $value
     */
    protected function addToMemory(string $key, $value): void 
    {
        if ($this->useMemoization()) {
            $this->memory[$key] = $value;
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    protected function getFromMemory(string $key)
    {
        if ($this->useMemoization()) {
            return $this->memory[$key] ?? null;
        }

        return null;
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
        if ($this->useMemoization()) {
            $this->memory = array_merge($this->memory, $values);
        }
    }

    /**
     * @param array $keys
     * @return array
     */
    protected function searchKeys(array $keys): array
    {
        if (!$this->useMemoization()) {
            return [[], $keys];
        }

        $found = $notFound = [];

        foreach($keys as $key) {
            if (isset($this->memory[$key])) {
                $found[$key] = $this->memory[$key];
            } else {
                $notFound[$key] = null;
            }
        }

        return [$found,$notFound];
    }

    protected function cleanMemory(): void
    {
        $this->memory = [];
    }

    /**
     * @return bool
     */
    protected function useMemoization() {
        return $this->use;
    }
}