<?php

namespace Level23\Dynadot\Dto;

final class DomainInfo implements DtoInterface
{
    public string $domainName;
    public int $expiration;
    public int $registration;
    /** @var array<string, string> */
    public array $glueInfo;
    public int $registrantContactId;
    public int $adminContactId;
    public int $techContactId;
    public int $billingContactId;
    public bool $locked;
    public bool $disabled;
    public bool $udrpLocked;
    public bool $registrantUnverified;
    public bool $hold;
    public string $privacy;
    public bool $isForSale;
    public string $renewOption;
    public ?string $note;
    public int $folderId;
    public string $folderName;
    public string $status;

    /**
     * @param array<string, string> $glueInfo
     */
    private function __construct(
        string $domainName,
        int $expiration,
        int $registration,
        array $glueInfo,
        int $registrantContactId,
        int $adminContactId,
        int $techContactId,
        int $billingContactId,
        bool $locked,
        bool $disabled,
        bool $udrpLocked,
        bool $registrantUnverified,
        bool $hold,
        string $privacy,
        bool $isForSale,
        string $renewOption,
        ?string $note,
        int $folderId,
        string $folderName,
        string $status,
    ) {
        $this->domainName           = $domainName;
        $this->expiration           = $expiration;
        $this->registration         = $registration;
        $this->glueInfo             = $glueInfo;
        $this->registrantContactId  = $registrantContactId;
        $this->adminContactId       = $adminContactId;
        $this->techContactId        = $techContactId;
        $this->billingContactId     = $billingContactId;
        $this->locked               = $locked;
        $this->disabled             = $disabled;
        $this->udrpLocked           = $udrpLocked;
        $this->registrantUnverified = $registrantUnverified;
        $this->hold                 = $hold;
        $this->privacy              = $privacy;
        $this->isForSale            = $isForSale;
        $this->renewOption          = $renewOption;
        $this->note                 = $note;
        $this->folderId             = $folderId;
        $this->folderName           = $folderName;
        $this->status               = $status;
    }

    /**
     * Hydrate from domain data.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['domainName'],
            $data['expiration'],
            $data['registration'],
            $data['glueInfo'],
            $data['registrant_contactId'],
            $data['admin_contactId'],
            $data['tech_contactId'],
            $data['billing_contactId'],
            $data['locked'],
            $data['disabled'],
            $data['udrpLocked'],
            $data['registrant_unverified'] ?? false,
            $data['hold'],
            $data['privacy'],
            $data['is_for_sale'],
            $data['renew_option'],
            $data['note'],
            $data['folder_id'],
            $data['folder_name'],
            $data['status'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domainName'            => $this->domainName,
            'expiration'            => $this->expiration,
            'registration'          => $this->registration,
            'glueInfo'              => $this->glueInfo,
            'registrant_contact_id' => $this->registrantContactId,
            'admin_contact_id'      => $this->adminContactId,
            'tech_contact_id'       => $this->techContactId,
            'billing_contact_id'    => $this->billingContactId,
            'locked'                => $this->locked,
            'disabled'              => $this->disabled,
            'udrp_locked'           => $this->udrpLocked,
            'registrant_unverified' => $this->registrantUnverified,
            'hold'                  => $this->hold,
            'privacy'               => $this->privacy,
            'is_for_sale'           => $this->isForSale,
            'renew_option'          => $this->renewOption,
            'note'                  => $this->note,
            'folder_id'             => $this->folderId,
            'folder_name'           => $this->folderName,
            'status'                => $this->status,
        ];
    }
}
