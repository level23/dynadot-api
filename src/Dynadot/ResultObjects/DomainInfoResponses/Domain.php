<?php
/**
 * Created by PhpStorm.
 * User: niek
 * Date: 15-12-16
 * Time: 13:28
 */

namespace Level23\Dynadot\ResultObjects\DomainInfoResponses;

class Domain
{
    public $Name;
    public $Expiration;
    public $Registration;

    /**
     * @var array
     */
    public $NameServerSettings;
    /**
     * @var Whois
     */
    public $Whois;
    public $Locked;
    public $Disabled;
    public $UdrpLocked;
    public $RegistrantUnverified;
    public $Hold;
    public $Privacy;
    public $isForSale;
    public $RenewOption;
    public $Note;
    public $Folder;
}
