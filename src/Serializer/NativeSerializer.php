<?php

namespace Natso\Serializer;

class NativeSerializer implements SerializerInterface
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