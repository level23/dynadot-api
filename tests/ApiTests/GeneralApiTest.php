<?php

namespace Level23\Dynadot\ApiTests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\DynadotApi;
use Level23\Dynadot\Exception\ApiHttpCallFailedException;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

class GeneralApiTests extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if fetching the API key after instantiating the API works.
     */
    public function testApiKey()
    {
        $apiKey = 'bla';
        $api = new DynadotApi($apiKey);

        $this->assertEquals($apiKey, $api->getApiKey());
    }

    /**
     * Test how the API handles a 404 response.
     *
     * It should throw an exception.
     */
    public function testApi404()
    {
        $mockHandler = new MockHandler([
            new Response(404)
        ]);

        $api = new DynadotApi('_API_KEY_GOES_HERE_');
        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->setExpectedException(ApiHttpCallFailedException::class);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = $api->getDomainInfo('example.com');
    }

    public function testLogger()
    {
        // Create the logger
        $logger = new Logger('my_logger');

        $logger->pushHandler(new NullHandler());
        $api = new DynadotApi('_API_KEY_GOES_HERE_', $logger);

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/validDomainInfoResponseBody.txt'
                )
            )
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        // in this case, we pretend example.com isn't owned by us
        $api->getDomainInfo('example.com');
    }
}
