<?php

namespace Level23\Dynadot\Dto;

final class SearchResult implements DtoInterface
{
    /**
     * @param array<PriceList> $priceList
     */
    public function __construct(
        public string $domainName,
        public string $available,
        public string $premium,
        public array $priceList,
    ) {
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
            'available'   => $this->available,
            'premium'     => $this->premium,
            'price_list'  => array_map(fn (PriceList $price) => $price->jsonSerialize(), $this->priceList),
        ];
    }
}
