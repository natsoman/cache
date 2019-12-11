<?php

namespace Natso\Compressor;

interface CompressorInterface
{

    /**
     * @param string $value
     * @return string
     */
    public function compress(string $value): string;

    /**
     * @param string $value
     * @return string
     */
    public function uncompress(string $value): string;
}