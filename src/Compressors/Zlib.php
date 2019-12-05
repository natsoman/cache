<?php

namespace Natso\Compressors;

use Natso\Interfaces\CompressorInterface;

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
    protected $level;
    
    /**
     * @inheritdoc
     */
    public function compress(?string $value): string
    {
        return gzcompress($value, $this->level);
    }

    /**
     * @inheritdoc
     */
    public function uncompress(?string $value)
    {
        if ($value !== null) {
            return gzuncompress($value);
        }

        return null;
    }
}