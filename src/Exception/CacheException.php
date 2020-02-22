<?php

namespace Natso\Exception;

use Throwable;
use Exception;
use Psr\SimpleCache\CacheException as CacheExceptionInterface;

class CacheException extends Exception implements CacheExceptionInterface {

    /**
     * @var string
     */
    protected $message = 'Caching service is not available';

    /**
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->message = $message ?? $this->message;
        parent::__construct($message, $code, $previous);
    }
}