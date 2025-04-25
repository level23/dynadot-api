<?php
namespace Level23\Dynadot\ResultObjects\SetNsResponse;

class SetNsHeader
{
    /**
     * @var int
     */
    public int $SuccessCode = -1;
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
