<?php
/**
 * Created by PhpStorm.
 * User: niek
 * Date: 16-12-16
 * Time: 09:29
 */

namespace Level23\Dynadot\ResultObjects\ListDomainInfoResponses;

class Domain
{
    public $Name;
    public $Expiration;
    public $Registration;

    /**
     * @var array
     */
    public $NameServerSettings;
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
