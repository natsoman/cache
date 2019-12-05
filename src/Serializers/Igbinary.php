<?php

namespace Natso\Serializers;

use Natso\Interfaces\SerializerInterface;

class Igbinary implements SerializerInterface
{

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        return igbinary_serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value)
    {
        return igbinary_unserialize($value);
    }
}