<?php

namespace Level23\Dynadot\ResultObjects\ListDomainInfoResponse;

class ListDomainInfoHeader
{
    /**
     * @var int
     */
    public int $ResponseCode = -1;

    /**
     * @var string
     */
    public string $Status = "";

    /**
     * @var string
     */
    public string $Error = "";

    const RESPONSECODE_OK = 0;
    const RESPONSECODE_FAILURE = -1;
}
