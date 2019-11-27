<?php

namespace  Epignosis\Interfaces;

use Epignosis\Exceptions\InvalidKeyException;

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