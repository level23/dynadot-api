<?php

namespace Level23\Dynadot\Tests\ApiTests;

use Monolog\Logger;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\DynadotApi;
use GuzzleHttp\Handler\MockHandler;
use Level23\Dynadot\Exception\ApiHttpCallFailedException;

class GeneralApiTest extends TestCase
{
    /**
     * Test if fetching the API key after instantiating the API works.
     */
    public function testApiKey(): void
    {
        $apiKey = 'bla';
        $api    = new DynadotApi($apiKey);

        $this->assertEquals($apiKey, $api->getApiKey());
    }

    /**
     * Test how the API handles a 404 response.
     *
     * It should throw an exception.
     */
    public function testApi404(): void
    {
        $mockHandler = new MockHandler([
            new Response(404),
        ]);

        $api = new DynadotApi('_API_KEY_GOES_HERE_');
        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->expectException(ApiHttpCallFailedException::class);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = $api->getDomainInfo('example.com');
    }

    public function testLogger(): void
    {
        // Create the logger
        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();

        $logger->expects($this->atLeastOnce())
            ->method('log');

        $api = new DynadotApi('_API_KEY_GOES_HERE_', $logger);

        $mockHandler = $this->getMockedResponse('validDomainInfoResponseBody.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        // in this case, we pretend we do not own example.com
        $api->getDomainInfo('example.com');
    }
}
