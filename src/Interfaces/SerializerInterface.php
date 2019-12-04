<?php

namespace Epignosis\Interfaces;

interface SerializerInterface
{

    /**
     * @param $value
     * @return string
     */
    public function serialize($value): string;

    /**
     * @param string $value
     * @return mixed
     */
    public function deserialize(string $value);
}