<?php

declare(strict_types=1);

namespace  Epignosis\Interfaces;

interface KeyBuilderInterface {

    /**
     * @param array|callable $namespace
     * @param array $map
     */
    public function __construct($namespace ,array $map);

    /**
     * @param string $key
     * @return mixed
     */
    public function build(string $key);
}