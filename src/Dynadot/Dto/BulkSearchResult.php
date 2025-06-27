<?php

namespace Level23\Dynadot\Dto;

final class BulkSearchResult implements DtoInterface
{
    /** @var array<BulkSearchDomainResult> */
    public array $domainResults;

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
        $domainResults = [];
        
        if (isset($data['domain_result_list']) && is_array($data['domain_result_list'])) {
            foreach ($data['domain_result_list'] as $domainData) {
                $domainResults[] = BulkSearchDomainResult::fromArray($domainData);
            }
        }
        
        $dto->domainResults = $domainResults;
        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domain_result_list' => array_map(fn($domain) => $domain->jsonSerialize(), $this->domainResults),
        ];
    }
} 