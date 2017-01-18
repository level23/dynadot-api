<?php

namespace Level23\Dynadot\ResultObjects\DomainInfoResponse;

class DomainInfoResponseHeader
{
    const SUCCESSCODE_OK = 0;
    const SUCCESSCODE_FAILURE = -1;

    /**
     * @var int
     */
    public $SuccessCode;

    /**
     * @var string
     */
    public $Status;

    /**
     * @var string
     */
    public $Error;
}
