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

    public Contact $registrant_contact;
    public Contact $admin_contact;
    public Contact $tech_contact;
    public Contact $billing_contact;

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
        Contact $registrant,
        Contact $admin,
        Contact $tech,
        Contact $billing,
        array $nameserverList,
        string $privacy,
        string $currency,
        bool $registerPremium,
        ?string $couponCode
    ) {
        if ($duration < 1 || $duration > 10) {
            throw new InvalidArgumentException('Duration must be between 1 and 10 years.');
        }

        $this->duration           = $duration;
        $this->authCode           = $authCode;
        $this->customerId         = $customerId;
        $this->registrant_contact = $registrant;
        $this->admin_contact      = $admin;
        $this->tech_contact       = $tech;
        $this->billing_contact    = $billing;
        $this->nameserverList     = $nameserverList;
        $this->privacy            = $privacy;
        $this->currency           = $currency;
        $this->register_premium   = $registerPremium;
        $this->coupon_code        = $couponCode;
    }

    /**
     * @param array<string> $nameserverList
     */
    public static function create(
        int $duration,
        string $authCode,
        int $customerId,
        Contact $registrant,
        Contact $admin,
        Contact $tech,
        Contact $billing,
        array $nameserverList,
        string $privacy = 'false',
        string $currency = 'USD',
        bool $registerPremium = false,
        ?string $couponCode = null
    ): self {
        return new self(
            $duration,
            $authCode,
            $customerId,
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
        return [
            'domain' => [
                'duration'                => $this->duration,
                'auth_code'               => $this->authCode,
                'registrant_contact_id'   => $this->customerId,
                'admin_contact_id'        => $this->admin_contact,
                'tech_contact_id'         => $this->tech_contact,
                'billing_contact_id'      => $this->billing_contact,
                'registrant_contact'      => $this->registrant_contact->jsonSerialize(),
                'admin_contact'           => $this->admin_contact->jsonSerialize(),
                'tech_contact'            => $this->tech_contact->jsonSerialize(),
                'billing_contact'         => $this->billing_contact->jsonSerialize(),
                'customer_id'             => $this->customerId,
                'name_server_list'        => $this->nameserverList,
                'privacy'                 => $this->privacy,
            ],
            'currency'         => $this->currency,
            'register_premium' => $this->register_premium,
            'coupon_code'      => $this->coupon_code,
        ];
    }
}
