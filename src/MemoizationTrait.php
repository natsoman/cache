<?php
namespace Epignosis;

trait MemoizationTrait {

    /**
     * @var array
     */
    protected $memory = [];

    /**
     * @param string $key
     * @param $value
     */
    protected function addToMemory(string $key, $value): void {
        $this->memory[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    protected function getFromMemory(string $key) {
        return $this->memory[$key] ?? null;
    }

    /**
     * @param string $key
     */
    protected function deleteFromMemory(string $key): void {
        unset($this->memory[$key]);
    }

    /**
     * @param array $values Associative array
     */
    protected function setToMemory(array $values): void {
        $this->memory = array_merge($this->memory, $values);
    }

    protected function cleanMemory():? void {
        $this->memory = [];
    }
}