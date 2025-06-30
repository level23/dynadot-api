<?php

namespace Level23\Dynadot\Dto;

final class SearchResult implements DtoInterface
{
    public string $domainName;
    public string $available;
    public string $premium;
    /** @var array<PriceList> */
    public array $priceList;

    /**
     * @param array<PriceList> $priceList
     */
    private function __construct(
        string $domainName,
        string $available,
        string $premium,
        array $priceList,
    ) {
        $this->domainName = $domainName;
        $this->available = $available;
        $this->premium = $premium;
        $this->priceList = $priceList;
    }

    /**
     * Hydrate from Dynadot's response data.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $priceList = [];
        if (isset($data['price_list']) && is_array($data['price_list'])) {
            foreach ($data['price_list'] as $priceData) {
                $priceList[] = PriceList::fromArray($priceData);
            }
        }

        return new self(
            $data['domain_name'] ?? '',
            $data['available'] ?? '',
            $data['premium'] ?? '',
            $priceList,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domain_name' => $this->domainName,
            'available' => $this->available,
            'premium' => $this->premium,
            'price_list' => array_map(fn($price) => $price->jsonSerialize(), $this->priceList),
        ];
    }
} 