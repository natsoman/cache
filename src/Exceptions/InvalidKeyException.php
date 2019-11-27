<?php

namespace Epignosis\Exceptions;

use Throwable;
use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidKeyException extends Exception implements InvalidArgumentException {

    protected $message = 'Key type is not valid';

    public function __construct(string $message = null, int $code = 0, Throwable $previous = null)
    {
        $this->message = $message ?? $this->message;
        parent::__construct($message, $code, $previous);
    }
}