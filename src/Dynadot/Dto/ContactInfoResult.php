<?php

namespace Level23\Dynadot\Dto;

final class ContactInfoResult implements DtoInterface
{
    public ?int $contactId;
    public string $organization;
    public string $name;
    public string $email;
    public string $phoneNumber;
    public string $phoneCc;
    public string $faxNumber;
    public string $faxCc;
    public string $address1;
    public string $address2;
    public string $city;
    public string $state;
    public string $zip;
    public string $country;

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
        
        $dto->contactId = $data['contact_id'] ?? null;
        $dto->organization = $data['organization'] ?? '';
        $dto->name = $data['name'] ?? '';
        $dto->email = $data['email'] ?? '';
        $dto->phoneNumber = $data['phone_number'] ?? '';
        $dto->phoneCc = $data['phone_cc'] ?? '';
        $dto->faxNumber = $data['fax_number'] ?? '';
        $dto->faxCc = $data['fax_cc'] ?? '';
        $dto->address1 = $data['address1'] ?? '';
        $dto->address2 = $data['address2'] ?? '';
        $dto->city = $data['city'] ?? '';
        $dto->state = $data['state'] ?? '';
        $dto->zip = $data['zip'] ?? '';
        $dto->country = $data['country'] ?? '';
        
        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'contact_id' => $this->contactId,
            'organization' => $this->organization,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
            'phone_cc' => $this->phoneCc,
            'fax_number' => $this->faxNumber,
            'fax_cc' => $this->faxCc,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
        ];
    }
} 