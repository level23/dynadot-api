<?php

namespace Level23\Dynadot\ResultObjects\DomainInfoResponse;

class DomainInfoHeader
{
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

    const SUCCESSCODE_OK = 0;
    const SUCCESSCODE_FAILURE = -1;
}
