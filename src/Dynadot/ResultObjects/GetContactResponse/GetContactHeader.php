<?php

namespace Level23\Dynadot\ResultObjects\GetContactResponse;

class GetContactHeader
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
