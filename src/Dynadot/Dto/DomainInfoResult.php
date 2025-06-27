<?php

namespace Level23\Dynadot\Dto;

final class DomainInfoResult implements DtoInterface
{
    /** @var array<DomainInfo> */
    public array $domains;

    private function __construct() {}

    /**
     * Hydrate from Dynadot's response "Data" object.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $domains = [];
        
        if (isset($data['domainInfo']) && is_array($data['domainInfo'])) {
            foreach ($data['domainInfo'] as $domainData) {
                $domains[] = DomainInfo::fromArray($domainData);
            }
        }
        
        $dto->domains = $domains;
        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domainInfo' => array_map(fn($domain) => $domain->jsonSerialize(), $this->domains),
        ];
    }
} 