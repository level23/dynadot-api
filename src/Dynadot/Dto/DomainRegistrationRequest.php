<?php

namespace Level23\Dynadot\Dto;

use InvalidArgumentException;
use JsonSerializable;

class DomainRegistrationRequest implements JsonSerializable
{
    private int $duration;
    private string $authCode;
    private int $customerId;
    /** @var array<string> */
    private array $nameserverList;
    private string $privacy;

    public ?int $registrant_contact_id;
    public ?int $admin_contact_id;
    public ?int $tech_contact_id;
    public ?int $billing_contact_id;

    public ?Contact $registrant_contact;
    public ?Contact $admin_contact;
    public ?Contact $tech_contact;
    public ?Contact $billing_contact;

    public string $currency;
    public bool   $register_premium;
    public ?string $coupon_code;

    /**
     * @param array<string> $nameserverList
     */
    private function __construct(
        int $duration,
        string $authCode,
        int $customerId,
        ?int $registrantContactId,
        ?int $adminContactId,
        ?int $techContactId,
        ?int $billingContactId,
        ?Contact $registrant,
        ?Contact $admin,
        ?Contact $tech,
        ?Contact $billing,
        array $nameserverList,
        string $privacy,
        string $currency,
        bool $registerPremium,
        ?string $couponCode
    ) {
        if ($duration < 1 || $duration > 10) {
            throw new InvalidArgumentException('Duration must be between 1 and 10 years.');
        }

        $this->duration = $duration;
        $this->authCode = $authCode;
        $this->customerId = $customerId;
        $this->registrant_contact_id = $registrantContactId;
        $this->admin_contact_id = $adminContactId;
        $this->tech_contact_id = $techContactId;
        $this->billing_contact_id = $billingContactId;
        $this->registrant_contact = $registrant;
        $this->admin_contact = $admin;
        $this->tech_contact = $tech;
        $this->billing_contact = $billing;
        $this->nameserverList = $nameserverList;
        $this->privacy = $privacy;
        $this->currency = $currency;
        $this->register_premium = $registerPremium;
        $this->coupon_code = $couponCode;
    }

    /**
     * @param array<string> $nameserverList
     */
    public static function create(
        int $duration,
        string $authCode,
        int $customerId,
        array $nameserverList,
        string $privacy = 'off',
        string $currency = 'USD',
        bool $registerPremium = false,
        ?string $couponCode = null,
        ?int $registrantContactId = null,
        ?int $adminContactId = null,
        ?int $techContactId = null,
        ?int $billingContactId = null,
        ?Contact $registrant = null,
        ?Contact $admin = null,
        ?Contact $tech = null,
        ?Contact $billing = null
    ): self {
        return new self(
            $duration,
            $authCode,
            $customerId,
            $registrantContactId,
            $adminContactId,
            $techContactId,
            $billingContactId,
            $registrant,
            $admin,
            $tech,
            $billing,
            $nameserverList,
            $privacy,
            $currency,
            $registerPremium,
            $couponCode
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $domain = [
            'duration'                => $this->duration,
            'auth_code'               => $this->authCode,
            'registrant_contact_id'   => $this->registrant_contact_id,
            'admin_contact_id'        => $this->admin_contact_id,
            'tech_contact_id'         => $this->tech_contact_id,
            'billing_contact_id'      => $this->billing_contact_id,
            'registrant_contact'      => $this->registrant_contact?->jsonSerialize(),
            'admin_contact'           => $this->admin_contact?->jsonSerialize(),
            'tech_contact'            => $this->tech_contact?->jsonSerialize(),
            'billing_contact'         => $this->billing_contact?->jsonSerialize(),
            'customer_id'             => $this->customerId,
            'name_server_list'        => $this->nameserverList,
            'privacy'                 => $this->privacy,
        ];

        return [
            'domain'           => array_filter($domain, fn($value) => $value !== null),
            'currency'         => $this->currency,
            'register_premium' => $this->register_premium,
            'coupon_code'      => $this->coupon_code,
        ];
    }
}
