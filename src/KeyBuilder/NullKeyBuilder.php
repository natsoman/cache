<?php

namespace Natso\KeyBuilder;

use Natso\Exception\InvalidKeyException;

class NullKeyBuilder implements KeyBuilderInterface
{
    /**
     * @inheritDoc
     */
    public function build(string $key, ...$args): string
    {
        return $key;
    }
}