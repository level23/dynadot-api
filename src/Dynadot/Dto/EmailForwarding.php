<?php

namespace Level23\Dynadot\Dto;

final class EmailForwarding implements DtoInterface
{
    public function __construct(
        public string $type = '',
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'] ?? ''
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
        ];
    }
}
