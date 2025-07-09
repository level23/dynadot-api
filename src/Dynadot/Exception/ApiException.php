<?php

declare(strict_types=1);

namespace Level23\Dynadot\Exception;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ApiException extends Exception
{
    private RequestInterface $request;
    private ?ResponseInterface $response;

    public function __construct(
        string $message,
        int $code,
        RequestInterface $request,
        ?ResponseInterface $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->request  = $request;
        $this->response = $response;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * HTTP status code from the response, if available.
     */
    public function getStatusCode(): ?int
    {
        return $this->response?->getStatusCode();
    }

    /**
     * Raw response body as string, if available.
     */
    public function getRawBody(): ?string
    {
        return $this->response ? (string) $this->response->getBody() : null;
    }

    /**
     * Helper to build the right subclass from a 4xx/5xx response.
     */
    public static function fromResponse(
        RequestInterface $req,
        ResponseInterface $res,
        ?Throwable $prev = null
    ): self {
        $body = (string) $res->getBody();
        $data = json_decode($body, true);
        $code = $data['code'] ?? $res->getStatusCode();

        if (json_last_error() !== JSON_ERROR_NONE) {
            $msg = 'Invalid JSON in error response: ' . json_last_error_msg();
        } else {
            $msg = $data['error']['description'] ?? $data['message'] ?? $res->getReasonPhrase();
        }

        switch ($code) {
            case 400:
                return new ValidationException($msg, $code, $req, $res, $prev);
            case 401:
            case 403:
                return new AuthenticationException($msg, $code, $req, $res, $prev);
            case 404:
                return new NotFoundException($msg, $code, $req, $res, $prev);
            default:
                return new self($msg, $code, $req, $res, $prev);
        }
    }
}
