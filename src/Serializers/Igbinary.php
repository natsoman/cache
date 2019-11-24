<?php

declare(strict_types=1);

namespace Epignosis\Serializers;

use Epignosis\Interfaces\SerializerInterface;

class Igbinary implements SerializerInterface {
    
    /**
     * @inheritdoc
     */
    public function serialize($value):string 
    {
        return \igbinary_serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value) 
    {
        return \igbinary_serialize($value);
    }
}