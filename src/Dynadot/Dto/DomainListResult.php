<?php

namespace Level23\Dynadot\Dto;

final class DomainListResult implements DtoInterface
{
    /** @var array<DomainInfo> */
    public array $domains;

    /**
     * @param array<DomainInfo> $domains
     */
    private function __construct(array $domains)
    {
        $this->domains = $domains;
    }

    /**
     * Hydrate from Dynadot's response "Data" object.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $domains = [];

        if (isset($data['domainInfo']) && is_array($data['domainInfo'])) {
            foreach ($data['domainInfo'] as $domainData) {
                $domains[] = DomainInfo::fromArray($domainData);
            }
        }

        return new self($domains);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domains' => array_map(fn ($domain) => $domain->jsonSerialize(), $this->domains),
        ];
    }
}
