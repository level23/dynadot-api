<?php

namespace Level23\Dynadot\Dto;

final class BulkSearchDomainResult implements DtoInterface
{
    public string $domainName;
    public string $available;

    private function __construct() {}

    /**
     * Hydrate from Dynadot's response data.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->domainName = $data['domain_name'] ?? '';
        $dto->available = $data['available'] ?? '';
        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domain_name' => $this->domainName,
            'available' => $this->available,
        ];
    }
} 