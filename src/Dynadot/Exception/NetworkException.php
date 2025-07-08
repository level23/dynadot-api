<?php

namespace Level23\Dynadot\Exception;

use Exception;
use Throwable;

class NetworkException extends Exception
{
    public function __construct(
        string $message,
        int $code,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
