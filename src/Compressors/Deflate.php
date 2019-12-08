<?php

namespace Natso\Compressors;

use Natso\Interfaces\CompressorInterface;

class Deflate implements CompressorInterface
{
    /**
     * @var int The level of compression
     */
    protected $level;

    /**
     * @param int $level
     */
    public function __construct(int $level = 6)
    {
        $this->level = $level;
    }

    /**
     * @inheritdoc
     */
    public function compress(?string $value): string
    {
        return gzdeflate($value, $this->level);
    }

    /**
     * @inheritdoc
     */
    public function uncompress(string $value)
    {
        return gzinflate($value);
    }
}