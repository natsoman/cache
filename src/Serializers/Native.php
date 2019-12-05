<?php

namespace Natso\Serializers;

use Natso\Interfaces\SerializerInterface;

class Native implements SerializerInterface
{

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        return serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value)
    {
        return unserialize($value);
    }
}