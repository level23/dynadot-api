<?php

namespace Level23\Dynadot\Dto;

/**
 * Common interface for all Data Transfer Objects (DTOs)
 * in the Dynadot API client.
 */
interface DtoInterface extends \JsonSerializable
{
    /**
     * Hydrate a DTO from a raw associative array.
     *
     * @param array<mixed> $data  The decoded JSON payload
     * @return static            An instance of the DTO
     */
    public static function fromArray(array $data): self;
}
