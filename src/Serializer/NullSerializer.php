<?php

namespace Natso\Serializer;

class NullSerializer implements SerializerInterface
{

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (string)$value;
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value)
    {
        return $value;
    }
}