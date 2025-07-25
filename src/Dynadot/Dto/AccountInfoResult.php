<?php

namespace Level23\Dynadot\Dto;

final class AccountInfoResult implements DtoInterface
{
    public function __construct(public AccountInfo $accountInfo)
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            isset($data['account_info']) ? AccountInfo::fromArray($data['account_info']) : new AccountInfo()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'account_info' => $this->accountInfo->jsonSerialize(),
        ];
    }
}
