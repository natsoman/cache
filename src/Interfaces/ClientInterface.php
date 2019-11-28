<?php

namespace Epignosis\Interfaces;

use Epignosis\Exceptions\InvalidKeyException;

interface ClientInterface {

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
    public function set(string $key, $value, int $ttl = 0): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * @param array $keys
     * @return array
     * @throws InvalidKeyException
     */
    public function mGet(array $keys);

    /**
     * @param array $values
     * @return bool
     * @throws InvalidKeyException
     */
    public function mSet(array $values): bool;

    /**
     * @param array $keys
     * @return bool
     * @throws InvalidKeyException
     */
    public function mDelete(array $keys): bool;

	/**
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool;

    /**
     * @return KeyBuilderInterface
     */
    public function getKeyBuilder(): KeyBuilderInterface;
}