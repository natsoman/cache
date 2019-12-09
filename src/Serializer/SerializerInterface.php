<?php

namespace Natso\Serializer;

interface SerializerInterface
{

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value): string;

    /**
     * @param string $value
     * @return mixed
     */
    public function deserialize(string $value);
}