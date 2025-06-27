<?php
namespace Level23\Dynadot\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationException extends ApiException
{
    public function __construct(
        string $message,
        int $code,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $request, $response, $previous);
    }
} 