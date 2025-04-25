<?php
namespace Level23\Dynadot\ResultObjects\DomainInfoResponse;

class DomainInfoResponse
{
    /**
     * @var DomainInfoHeader|null
     */
    public ?DomainInfoHeader $DomainInfoHeader;

    /**
     * @var DomainInfoContent
     */
    public DomainInfoContent $DomainInfoContent;
}
