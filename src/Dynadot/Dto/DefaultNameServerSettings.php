<?php

namespace Level23\Dynadot\Dto;

final class DefaultNameServerSettings implements DtoInterface
{
    public string $type;
    public string $withAds;
    public string $forwardTo;
    public string $forwardType;
    public string $websiteTitle;
    public string $ttl;
    public EmailForwarding $emailForwarding;

    public function __construct(
        string $type = '',
        string $withAds = '',
        string $forwardTo = '',
        string $forwardType = '',
        string $websiteTitle = '',
        string $ttl = '',
        ?EmailForwarding $emailForwarding = null
    ) {
        $this->type            = $type;
        $this->withAds         = $withAds;
        $this->forwardTo       = $forwardTo;
        $this->forwardType     = $forwardType;
        $this->websiteTitle    = $websiteTitle;
        $this->ttl             = $ttl;
        $this->emailForwarding = $emailForwarding ?? new EmailForwarding();
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['type'] ?? '',
            $data['with_ads'] ?? '',
            $data['forward_to'] ?? '',
            $data['forward_type'] ?? '',
            $data['website_title'] ?? '',
            $data['ttl'] ?? '',
            isset($data['email_forwarding']) ? EmailForwarding::fromArray($data['email_forwarding']) : new EmailForwarding()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'type'             => $this->type,
            'with_ads'         => $this->withAds,
            'forward_to'       => $this->forwardTo,
            'forward_type'     => $this->forwardType,
            'website_title'    => $this->websiteTitle,
            'ttl'              => $this->ttl,
            'email_forwarding' => $this->emailForwarding->jsonSerialize(),
        ];
    }
}
