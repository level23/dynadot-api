<?php

namespace Level23\Dynadot\Dto;

final class DomainRegistrationResult implements DtoInterface
{
    public string $domainName;
    public int $expirationDate;

    private function __construct(
        string $domainName,
        int $expirationDate,
    ) {
        $this->domainName     = $domainName;
        $this->expirationDate = $expirationDate;
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
            $data['expiration_date'] ?? 0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domain_name'     => $this->domainName,
            'expiration_date' => $this->expirationDate,
        ];
    }
}
