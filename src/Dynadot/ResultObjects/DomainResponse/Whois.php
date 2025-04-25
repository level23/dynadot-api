<?php

namespace Level23\Dynadot\ResultObjects\DomainResponse;

class Whois
{
    /**
     * @var int
     */
    public int $Registrant;

    /**
     * @var int
     */
    public int $Admin;

    /**
     * @var int
     */
    public int $Technical;

    /**
     * @var int
     */
    public int $Billing;
}
