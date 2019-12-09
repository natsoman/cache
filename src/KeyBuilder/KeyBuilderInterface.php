<?php

namespace Natso;

use Natso\Exception\InvalidKeyException;

interface KeyBuilderInterface
{
    /**
     * @param string $key
     * @param mixed ...$args
     * @param InvalidKeyException
     * @return string
     */
    public function build(string $key, ...$args);
}