<?php

namespace Epignosis\Compressors;

use Epignosis\Interfaces\CompressorInterface;

class Zlib implements CompressorInterface {

    /**
     * @param int $level The level of compression
     */
    public function __construct(int $level = -1)
    {
        $this->level = $level;
    }

    /**
     * @var int
     */
    protected $level = 6;
    
    /**
     * @inheritdoc
     */
    public function compress($value): string
    {
        return gzcompress($value, $this->level);
    }

    /**
     * @inheritdoc
     */
    public function decompress($value)
    {
        return gzuncompress($value);
    }
}