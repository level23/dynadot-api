<?php

namespace Level23\Dynadot\Dto;

final class Contact implements DtoInterface
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

    private function __construct(
        ?int $contactId,
        string $organization,
        string $name,
        string $email,
        string $phoneNumber,
        string $phoneCc,
        string $faxNumber,
        string $faxCc,
        string $address1,
        string $address2,
        string $city,
        string $state,
        string $zip,
        string $country,
    ) {
        $this->contactId    = $contactId;
        $this->organization = $organization;
        $this->name         = $name;
        $this->email        = $email;
        $this->phoneNumber  = $phoneNumber;
        $this->phoneCc      = $phoneCc;
        $this->faxNumber    = $faxNumber;
        $this->faxCc        = $faxCc;
        $this->address1     = $address1;
        $this->address2     = $address2;
        $this->city         = $city;
        $this->state        = $state;
        $this->zip          = $zip;
        $this->country      = $country;
    }

    /**
     * Create a new Contact instance.
     *
     * @param int|null $contactId
     * @param string $organization
     * @param string $name
     * @param string $email
     * @param string $phoneNumber
     * @param string $phoneCc
     * @param string $address1
     * @param string $city
     * @param string $state
     * @param string $zip
     * @param string $country
     * @param string $address2
     * @param string $faxNumber
     * @param string $faxCc
     * @param int|null $contactId
     * @return self
     */
    public static function create(
        string $organization,
        string $name,
        string $email,
        string $phoneNumber,
        string $phoneCc,
        string $address1,
        string $city,
        string $state,
        string $zip,
        string $country,
        string $address2 = '',
        string $faxNumber = '',
        string $faxCc = '',
        ?int $contactId = null
    ): self {
        return new self(
            $contactId,
            $organization,
            $name,
            $email,
            $phoneNumber,
            $phoneCc,
            $faxNumber,
            $faxCc,
            $address1,
            $address2,
            $city,
            $state,
            $zip,
            $country,
        );
    }

    /**
     * Hydrate from Dynadot's response data.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['contact_id'] ?? null,
            $data['organization'] ?? '',
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['phone_number'] ?? '',
            $data['phone_cc'] ?? '',
            $data['fax_number'] ?? '',
            $data['fax_cc'] ?? '',
            $data['address1'] ?? '',
            $data['address2'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['zip'] ?? '',
            $data['country'] ?? '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            'organization' => $this->organization,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone_number' => $this->phoneNumber,
            'phone_cc'     => $this->phoneCc,
            'fax_number'   => $this->faxNumber,
            'fax_cc'       => $this->faxCc,
            'address1'     => $this->address1,
            'address2'     => $this->address2,
            'city'         => $this->city,
            'state'        => $this->state,
            'zip'          => $this->zip,
            'country'      => $this->country,
        ];

        // Only include contact_id if it's not null
        if ($this->contactId !== null) {
            $data['contact_id'] = $this->contactId;
        }

        return $data;
    }
}
