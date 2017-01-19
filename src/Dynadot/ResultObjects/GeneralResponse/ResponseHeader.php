<?php

namespace Level23\Dynadot\ResultObjects\GeneralResponse;

class ResponseHeader
{
    /**
     * @var int
     */
    public $ResponseCode;

    /**
     * @var String
     */
    public $Error;

    const RESPONSECODE_OK = 0;
    const RESPONSECODE_FAILURE = -1;
}