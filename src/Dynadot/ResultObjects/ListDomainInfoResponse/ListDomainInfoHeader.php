<?php

namespace Level23\Dynadot\ResultObjects\ListDomainInfoResponse;

class ListDomainInfoHeader
{
    /**
     * @var int
     */
    public $ResponseCode;

    /**
     * @var string
     */
    public $Status;

    /**
     * @var string
     */
    public $Error;

    const RESPONSECODE_OK = 0;
    const RESPONSECODE_FAILURE = -1;
}
