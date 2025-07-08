<?php

namespace Level23\Dynadot\Dto;

final class RenewOptionResult implements DtoInterface
{
    public ?int $code;
    public ?string $message;

    private function __construct(
        ?int $code,
        ?string $message,
    ) {
        $this->code    = $code;
        $this->message = $message;
    }

    /**
     * Hydrate from Dynadot's response "Data" object.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['code'] ?? null,
            $data['message'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'code'    => $this->code,
            'message' => $this->message,
        ];
    }
}
