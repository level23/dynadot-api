<?php

namespace Level23\Dynadot\ResultObjects\DomainResponse;

class NameServerSettings
{
    /**
     * @var string
     */
    public $Type;

    /**
     * @var NameServer[]
     */
    public $NameServers = [];

    /**
     * In case of Type "Dynadot Parking" this value gives indication if Advertisements are shown on the parking page.
     * @var boolean
     */
    public $WithAds;
}
