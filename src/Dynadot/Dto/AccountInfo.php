<?php

namespace Level23\Dynadot\Dto;

final class AccountInfo implements DtoInterface
{
    public string $username;
    public string $forumName;
    public string $avatarUrl;
    public AccountContact $accountContact;
    public int $customerSince;
    public string $accountLock;
    public string $customTimeZone;
    public int $defaultRegistrantContactId;
    public int $defaultAdminContactId;
    public int $defaultTechnicalContactId;
    public int $defaultBillingContactId;
    public DefaultNameServerSettings $defaultNameServerSettings;
    public string $totalSpending;
    public string $priceLevel;
    public string $accountBalance;
    /** @var array<BalanceListItem> */
    public array $balanceList;

    /**
     * @param array<BalanceListItem> $balanceList
     */
    public function __construct(
        string $username = '',
        string $forumName = '',
        string $avatarUrl = '',
        ?AccountContact $accountContact = null,
        int $customerSince = 0,
        string $accountLock = '',
        string $customTimeZone = '',
        int $defaultRegistrantContactId = 0,
        int $defaultAdminContactId = 0,
        int $defaultTechnicalContactId = 0,
        int $defaultBillingContactId = 0,
        ?DefaultNameServerSettings $defaultNameServerSettings = null,
        string $totalSpending = '',
        string $priceLevel = '',
        string $accountBalance = '',
        array $balanceList = []
    ) {
        $this->username                   = $username;
        $this->forumName                  = $forumName;
        $this->avatarUrl                  = $avatarUrl;
        $this->accountContact             = $accountContact ?? new AccountContact();
        $this->customerSince              = $customerSince;
        $this->accountLock                = $accountLock;
        $this->customTimeZone             = $customTimeZone;
        $this->defaultRegistrantContactId = $defaultRegistrantContactId;
        $this->defaultAdminContactId      = $defaultAdminContactId;
        $this->defaultTechnicalContactId  = $defaultTechnicalContactId;
        $this->defaultBillingContactId    = $defaultBillingContactId;
        $this->defaultNameServerSettings  = $defaultNameServerSettings ?? new DefaultNameServerSettings();
        $this->totalSpending              = $totalSpending;
        $this->priceLevel                 = $priceLevel;
        $this->accountBalance             = $accountBalance;
        $this->balanceList                = $balanceList;
    }

    public static function fromArray(array $data): self
    {
        $balanceList = [];
        if (isset($data['balance_list']) && is_array($data['balance_list'])) {
            foreach ($data['balance_list'] as $item) {
                $balanceList[] = BalanceListItem::fromArray($item);
            }
        }

        return new self(
            $data['username'] ?? '',
            $data['forum_name'] ?? '',
            $data['avatar_url'] ?? '',
            isset($data['account_contact']) ? AccountContact::fromArray($data['account_contact']) : new AccountContact(),
            $data['customer_since'] ?? 0,
            $data['account_lock'] ?? '',
            $data['custom_time_zone'] ?? '',
            $data['default_registrant_contact_id'] ?? 0,
            $data['default_admin_contact_id'] ?? 0,
            $data['default_technical_contact_id'] ?? 0,
            $data['default_billing_contact_id'] ?? 0,
            isset($data['default_name_server_settings']) ? DefaultNameServerSettings::fromArray($data['default_name_server_settings']) : new DefaultNameServerSettings(),
            $data['total_spending'] ?? '',
            $data['price_level'] ?? '',
            $data['account_balance'] ?? '',
            $balanceList
        );
    }

    public static function empty(): self
    {
        return new self();
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'username'                      => $this->username,
            'forum_name'                    => $this->forumName,
            'avatar_url'                    => $this->avatarUrl,
            'account_contact'               => $this->accountContact->jsonSerialize(),
            'customer_since'                => $this->customerSince,
            'account_lock'                  => $this->accountLock,
            'custom_time_zone'              => $this->customTimeZone,
            'default_registrant_contact_id' => $this->defaultRegistrantContactId,
            'default_admin_contact_id'      => $this->defaultAdminContactId,
            'default_technical_contact_id'  => $this->defaultTechnicalContactId,
            'default_billing_contact_id'    => $this->defaultBillingContactId,
            'default_name_server_settings'  => $this->defaultNameServerSettings->jsonSerialize(),
            'total_spending'                => $this->totalSpending,
            'price_level'                   => $this->priceLevel,
            'account_balance'               => $this->accountBalance,
            'balance_list'                  => array_map(fn ($item) => $item->jsonSerialize(), $this->balanceList),
        ];
    }
}
