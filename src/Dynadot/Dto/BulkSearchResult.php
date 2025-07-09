<?php

namespace Level23\Dynadot\Dto;

final class BulkSearchResult implements DtoInterface
{
    /** @var array<BulkSearchDomainResult> */
    public array $domainResults;

    /**
     * @param array<BulkSearchDomainResult> $domainResults
     */
    private function __construct(array $domainResults)
    {
        $this->domainResults = $domainResults;
    }

    /**
     * Hydrate from Dynadot's response "Data" object.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $domainResults = [];

        if (isset($data['domain_result_list']) && is_array($data['domain_result_list'])) {
            foreach ($data['domain_result_list'] as $domainData) {
                $domainResults[] = BulkSearchDomainResult::fromArray($domainData);
            }
        }

        return new self($domainResults);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domain_result_list' => array_map(fn ($domain) => $domain->jsonSerialize(), $this->domainResults),
        ];
    }
}
