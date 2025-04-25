<?php

namespace Level23\Dynadot\ResultObjects\DomainResponse;

class NameServerSettings
{
    /**
     * @var string|null
     */
    public ?string $Type = null;

    /**
     * @var array<int,string>
     */
    public array $NameServers = [];

    /**
     * In case of Type "Dynadot Parking" this value gives indication if Advertisements are shown on the parking page.
     * @var boolean
     */
    public bool $WithAds = false;
}
