<?php

namespace  Epignosis\Interfaces;

interface KeyBuilderInterface {

    /**
     * @param array $map
     */
    public function __construct(array $map);

    /**
     * @param string $key
     * @param mixed ...$args
     * @return string
     */
    public function build(string $key, ...$args);
}