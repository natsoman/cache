<?php

namespace Natso\Compressor;

class ZlibCompressor implements CompressorInterface
{
    /**
     * @var int The level of compression
     */
    protected $level;

    /**
     * @param int $level
     */
    public function __construct(int $level = -1)
    {
        $this->level = $level;
    }

    /**
     * @inheritdoc
     */
    public function compress(string $value): string
    {
        return gzcompress($value, $this->level);
    }

    /**
     * @inheritdoc
     */
    public function uncompress(string $value): string
    {
        return gzuncompress($value);
    }
}