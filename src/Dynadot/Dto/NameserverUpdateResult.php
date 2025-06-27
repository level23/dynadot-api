<?php

namespace Level23\Dynadot\Dto;

final class NameserverUpdateResult implements DtoInterface
{
    public int $code;
    public string $message;

    private function __construct() {}

    /**
     * Hydrate from Dynadot's response.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->code = $data['code'] ?? 0;
        $dto->message = $data['message'] ?? '';
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