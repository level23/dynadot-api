<?php

namespace Level23\Dynadot\ResultObjects\GeneralResponse;

class ResponseHeader
{
    /**
     * @var int
     */
    public int $ResponseCode = -1;

    /**
     * @var String
     */
    public string $Error = "";

    const RESPONSECODE_OK = 0;
    const RESPONSECODE_FAILURE = -1;
}
