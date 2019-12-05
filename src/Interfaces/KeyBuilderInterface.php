<?php

namespace  Natso\Interfaces;

use Natso\Exceptions\InvalidKeyException;

interface KeyBuilderInterface {

    /**
     * @param array $map
     */
    public function __construct(array $map);

    /**
     * @param string $key
     * @param mixed ...$args
     * @param InvalidKeyException
     * @return string
     */
    public function build(string $key, ...$args);
}