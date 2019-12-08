<?php

namespace Natso\Interfaces;

interface CompressorInterface
{

    /**
     * @param mixed $value
     * @return mixed
     */
    public function compress(?string $value): string;

    /**
     * @param string $value
     * @return mixed
     */
    public function uncompress(string $value);
}