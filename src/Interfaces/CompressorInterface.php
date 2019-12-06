<?php

namespace Natso\Interfaces;

interface CompressorInterface
{

    /**
     * @param mixed $value
     * @return mixed
     */
    public function compress(string $value);

    /**
     * @param mixed $value
     * @return mixed
     */
    public function uncompress(?string $value);
}