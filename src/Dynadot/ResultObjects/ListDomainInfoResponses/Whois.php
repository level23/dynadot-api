<?php
/**
 * Created by PhpStorm.
 * User: niek
 * Date: 20-12-16
 * Time: 12:06
 */

namespace Level23\Dynadot\ResultObjects\ListDomainInfoResponses;


class Whois
{
    /**
     * @var \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Registrant
     */
    public $Registrant;

    /**
     * @var \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Admin
     */
    public $Admin;

    /**
     * @var \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Technical
     */
    public $Technical;

    /**
     * @var \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Billing
     */
    public $Billing;
}