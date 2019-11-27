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
    protected $level;
    
    /**
     * @inheritdoc
     */
    public function compress($value): string
    {
        return ($value === null) ? null : gzcompress($value, $this->level);
    }

    /**
     * @inheritdoc
     */
    public function uncompress($value)
    {
        return ($value === null) ? null : gzuncompress($value);
    }
}