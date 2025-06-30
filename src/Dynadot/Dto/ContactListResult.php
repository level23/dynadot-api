<?php

namespace Level23\Dynadot\Dto;

final class ContactListResult implements DtoInterface
{
    /** @var array<Contact> */
    public array $contacts;

    /**
     * @param array<Contact> $contacts
     */
    private function __construct(array $contacts)
    {
        $this->contacts = $contacts;
    }

    /**
     * Hydrate from Dynadot's response "Data" object.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $contacts = [];
        
        if (isset($data['contact_list']) && is_array($data['contact_list'])) {
            foreach ($data['contact_list'] as $contactData) {
                $contacts[] = Contact::fromArray($contactData);
            }
        }
        
        return new self($contacts);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'contact_list' => array_map(fn($contact) => $contact->jsonSerialize(), $this->contacts),
        ];
    }
} 