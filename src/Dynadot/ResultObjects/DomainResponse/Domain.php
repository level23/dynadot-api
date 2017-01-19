<?php

namespace Level23\Dynadot\ResultObjects\DomainResponse;

class Domain
{
    /**
     * @var string
     */
    public $Name;

    /**
     * Unix timestamp
     * @var string
     */
    public $Expiration;

    /**
     * Unix timestamp
     * @var string
     */
    public $Registration;

    /**
     * @var NameServerSettings
     */
    public $NameServerSettings;

    /**
     * @var Whois
     */
    public $Whois;

    /**
     * @var boolean
     */
    public $Locked;

    /**
     * @var boolean
     */
    public $Disabled;

    /**
     * @var boolean
     */
    public $UdrpLocked;

    /**
     * @var boolean
     */
    public $RegistrantUnverified;

    /**
     * @var boolean
     */
    public $Hold;

    /**
     * Possible options (could be more):
     * - full
     * - none
     * @var string
     */
    public $Privacy;

    /**
     * @var boolean
     */
    public $isForSale;

    /**
     * @var string
     */
    public $RenewOption;

    /**
     * @var String
     */
    public $Note;

    /**
     * @var Folder
     */
    public $Folder;
}
