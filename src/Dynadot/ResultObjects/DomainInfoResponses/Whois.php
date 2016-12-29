<?php
/**
 * Created by PhpStorm.
 * User: niek
 * Date: 20-12-16
 * Time: 12:06
 */

namespace Level23\Dynadot\ResultObjects\DomainInfoResponses;


class Whois
{
    /**
     * @var \Level23\Dynadot\ResultObjects\DomainInfoResponses\Registrant
     */
    public $Registrant;

    /**
     * @var \Level23\Dynadot\ResultObjects\DomainInfoResponses\Admin
     */
    public $Admin;

    /**
     * @var \Level23\Dynadot\ResultObjects\DomainInfoResponses\Technical
     */
    public $Technical;

    /**
     * @var \Level23\Dynadot\ResultObjects\DomainInfoResponses\Billing
     */
    public $Billing;
}