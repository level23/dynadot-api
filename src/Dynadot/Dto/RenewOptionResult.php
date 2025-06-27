<?php

namespace Level23\Dynadot\Dto;

final class RenewOptionResult implements DtoInterface
{
    public ?int $code;
    public ?string $message;

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

        $dto->code = $data['code'] ?? null;
        $dto->message = $data['message'] ?? null;
        
        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
        ];
    }
} 