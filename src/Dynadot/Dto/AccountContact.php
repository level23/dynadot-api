<?php

namespace Level23\Dynadot\Dto;

final class AccountContact implements DtoInterface
{
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

    public function __construct(
        string $organization = '',
        string $name = '',
        string $email = '',
        string $phoneNumber = '',
        string $phoneCc = '',
        string $faxNumber = '',
        string $faxCc = '',
        string $address1 = '',
        string $address2 = '',
        string $city = '',
        string $state = '',
        string $zip = '',
        string $country = ''
    ) {
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

    public static function fromArray(array $data): self
    {
        return new self(
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
            $data['country'] ?? ''
        );
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
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
    }
}
