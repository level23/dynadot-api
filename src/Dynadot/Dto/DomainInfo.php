<?php

namespace Level23\Dynadot\Dto;

final class DomainInfo implements DtoInterface
{
    public string $domainName;
    public int $expiration;
    public int $registration;
    /** @var array<string, mixed> */
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

    private function __construct() {}

    /**
     * Hydrate from domain data.
     *
     * @param array<string,mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->domainName = $data['domainName'];
        $dto->expiration = $data['expiration'];
        $dto->registration = $data['registration'];
        $dto->glueInfo = $data['glueInfo'];
        $dto->registrantContactId = $data['registrant_contactId'];
        $dto->adminContactId = $data['admin_contactId'];
        $dto->techContactId = $data['tech_contactId'];
        $dto->billingContactId = $data['billing_contactId'];
        $dto->locked = $data['locked'];
        $dto->disabled = $data['disabled'];
        $dto->udrpLocked = $data['udrpLocked'];
        $dto->registrantUnverified = $data['registrant_unverified'];
        $dto->hold = $data['hold'];
        $dto->privacy = $data['privacy'];
        $dto->isForSale = $data['is_for_sale'];
        $dto->renewOption = $data['renew_option'];
        $dto->note = $data['note'];
        $dto->folderId = $data['folder_id'];
        $dto->folderName = $data['folder_name'];
        $dto->status = $data['status'];

        return $dto;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'domainName' => $this->domainName,
            'expiration' => $this->expiration,
            'registration' => $this->registration,
            'glueInfo' => $this->glueInfo,
            'registrant_contact_id' => $this->registrantContactId,
            'admin_contact_id' => $this->adminContactId,
            'tech_contact_id' => $this->techContactId,
            'billing_contact_id' => $this->billingContactId,
            'locked' => $this->locked,
            'disabled' => $this->disabled,
            'udrp_locked' => $this->udrpLocked,
            'registrant_unverified' => $this->registrantUnverified,
            'hold' => $this->hold,
            'privacy' => $this->privacy,
            'is_for_sale' => $this->isForSale,
            'renew_option' => $this->renewOption,
            'note' => $this->note,
            'folder_id' => $this->folderId,
            'folder_name' => $this->folderName,
            'status' => $this->status,
        ];
    }
} 