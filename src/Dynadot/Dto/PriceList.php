<?php

namespace Level23\Dynadot\Dto;

final class PriceList implements DtoInterface
{
    public string $currency;
    public string $unit;
    public string $transfer;
    public string $restore;

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
        $dto->currency = $data['currency'] ?? '';
        $dto->unit = $data['unit'] ?? '';
        $dto->transfer = $data['transfer'] ?? '';
        $dto->restore = $data['restore'] ?? '';
        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'currency' => $this->currency,
            'unit' => $this->unit,
            'transfer' => $this->transfer,
            'restore' => $this->restore,
        ];
    }
} 