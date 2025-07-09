<?php

namespace Level23\Dynadot\Dto;

final class BulkSearchDomainResult implements DtoInterface
{
    public string $domainName;
    public string $available;

    private function __construct(
        string $domainName,
        string $available,
    ) {
        $this->domainName = $domainName;
        $this->available  = $available;
    }

    /**
     * Hydrate from Dynadot's response data.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['domain_name'] ?? '',
            $data['available'] ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domain_name' => $this->domainName,
            'available'   => $this->available,
        ];
    }
}
