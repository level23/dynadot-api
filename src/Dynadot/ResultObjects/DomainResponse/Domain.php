<?php

namespace Level23\Dynadot\ResultObjects\DomainResponse;

class Domain
{
    /**
     * @var string
     */
    public string $Name;

    /**
     * Unix timestamp
     * @var string
     */
    public string $Expiration;

    /**
     * Unix timestamp
     * @var string
     */
    public string $Registration;

    /**
     * @var NameServerSettings
     */
    public NameServerSettings $NameServerSettings;

    /**
     * @var Whois
     */
    public Whois $Whois;

    /**
     * @var boolean
     */
    public bool $Locked;

    /**
     * @var boolean
     */
    public bool $Disabled;

    /**
     * @var boolean
     */
    public bool $UdrpLocked;

    /**
     * @var boolean
     */
    public bool $RegistrantUnverified;

    /**
     * @var boolean
     */
    public bool $Hold;

    /**
     * Possible options (could be more):
     * - full
     * - none
     * @var string
     */
    public string $Privacy;

    /**
     * @var boolean
     */
    public bool $isForSale;

    /**
     * @var string
     */
    public string $RenewOption;

    /**
     * @var string|null
     */
    public ?string $Note;

    /**
     * @var Folder
     */
    public Folder $Folder;
}
