<?php

namespace Level23\Dynadot\Dto;

use InvalidArgumentException;
use JsonSerializable;

class DomainRegistrationRequest implements JsonSerializable
{
    /**
     * @param array<string> $nameserverList
     */
    public function __construct(
        public int $duration,
        public string $authCode,
        public int $customerId,
        public ?int $registrantContactId,
        public ?int $adminContactId,
        public ?int $techContactId,
        public ?int $billingContactId,
        public ?Contact $registrant,
        public ?Contact $admin,
        public ?Contact $tech,
        public ?Contact $billing,
        public array $nameserverList,
        public string $privacy,
        public string $currency,
        public bool $registerPremium,
        public ?string $couponCode
    ) {
        if ($duration < 1 || $duration > 10) {
            throw new InvalidArgumentException('Duration must be between 1 and 10 years.');
        }
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
            'duration'              => $this->duration,
            'auth_code'             => $this->authCode,
            'registrant_contact_id' => $this->registrantContactId,
            'admin_contact_id'      => $this->adminContactId,
            'tech_contact_id'       => $this->techContactId,
            'billing_contact_id'    => $this->billingContactId,
            'registrant_contact'    => $this->registrant?->jsonSerialize(),
            'admin_contact'         => $this->admin?->jsonSerialize(),
            'tech_contact'          => $this->tech?->jsonSerialize(),
            'billing_contact'       => $this->billing?->jsonSerialize(),
            'customer_id'           => $this->customerId,
            'name_server_list'      => $this->nameserverList,
            'privacy'               => $this->privacy,
        ];

        return [
            'domain'           => array_filter($domain, fn ($value) => $value !== null),
            'currency'         => $this->currency,
            'register_premium' => $this->registerPremium,
            'coupon_code'      => $this->couponCode,
        ];
    }
}
