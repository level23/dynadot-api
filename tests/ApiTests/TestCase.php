<?php

namespace Level23\Dynadot\Tests\ApiTests;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Return a mocked response.
     * @param string $filename
     * @param int    $httpStatus
     *
     * @return \GuzzleHttp\Handler\MockHandler
     */
    protected function getMockedResponse(string $filename, int $httpStatus = 200): MockHandler
    {
        return new MockHandler([
            new Response(
                $httpStatus,
                [],
                (string)file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/' . $filename
                )
            ),
        ]);
    }
}