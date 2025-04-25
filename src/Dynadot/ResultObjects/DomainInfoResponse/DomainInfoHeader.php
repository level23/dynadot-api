<?php

namespace Level23\Dynadot\ResultObjects\DomainInfoResponse;

class DomainInfoHeader
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

    const SUCCESSCODE_OK = 0;
    const SUCCESSCODE_FAILURE = -1;
}
