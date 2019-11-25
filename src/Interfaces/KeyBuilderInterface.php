<?php

declare(strict_types=1);

namespace  Epignosis\Interfaces;

interface KeyBuilderInterface {

    /**
     * @param array $map
     */
    public function __construct(array $map);

    /**
     * @param string $key
     * @return mixed
     */
    public function build(string $key);
}