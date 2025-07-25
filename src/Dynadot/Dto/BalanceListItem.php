<?php

namespace Level23\Dynadot\Dto;

final class BalanceListItem implements DtoInterface
{
    public function __construct(
        public string $currency = '',
        public string $amount = ''
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['currency'] ?? '',
            $data['amount'] ?? ''
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'currency' => $this->currency,
            'amount'   => $this->amount,
        ];
    }
}
