<?php

namespace Level23\Dynadot\Dto;

final class BalanceListItem implements DtoInterface
{
    public string $currency;
    public string $amount;

    public function __construct(string $currency = '', string $amount = '')
    {
        $this->currency = $currency;
        $this->amount   = $amount;
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
