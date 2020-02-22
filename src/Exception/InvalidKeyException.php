<?php

namespace Natso\Exception;

use Throwable;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidKeyException extends Exception implements InvalidArgumentException
{
    /**
     * @var string
     */
    protected $message = 'Key type is not valid';

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