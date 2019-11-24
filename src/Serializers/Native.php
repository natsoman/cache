<?php

declare(strict_types=1);

namespace Epignosis\Serializers;

use Epignosis\Interfaces\SerializerInterface;

class Native implements SerializerInterface {
    
    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        return \serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value)
    {
        return \unserialize($value);
    }
}