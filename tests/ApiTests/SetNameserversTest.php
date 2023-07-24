<?php

namespace Level23\Dynadot\Tests\ApiTests;

use GuzzleHttp\Psr7\Response;
use Sabre\Xml\LibXMLException;
use Level23\Dynadot\DynadotApi;
use GuzzleHttp\Handler\MockHandler;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\Exception\ApiLimitationExceededException;

class SetNameserversTest extends TestCase
{
    /**
     * Test setting nameservers for a domain.
     */
    public function testSetNameservers(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('validSetNsResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $api->setNameserversForDomain('example.com', ['ns01.example.com', 'ns02.example.com']);

        // If something nasty happend, we should not reach this.
        $this->assertTrue(true);
    }

    /**
     * Test setting nameservers for a domain.
     */
    public function testInvalidResponse(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidSetNsResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->expectException(DynadotApiException::class);
        $api->setNameserversForDomain('example.com', ['ns01.example.com', 'ns02.example.com']);
    }

    /**
     * Test if some limitations the API has for setNs calls are respected and the proper exceptions are thrown.
     */
    public function testApiLimitations(): void
    {
        // set up mock objects
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        // the response should not matter in our test, so we are just going to fake a 404
        // we should get an exception even before receiving a HTTP response.
        // however, we should still specify a mock handler so in case this *doesn't* work, we don't perform an actual
        // API call.
        $mockHandler = new MockHandler([
            new Response(
                404
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        // we are going to expect a ApiLimitationExceededException if we try to set 14 nameservers
        $this->expectException(ApiLimitationExceededException::class);

        // try to set 14 nameservers for example.com
        $api->setNameserversForDomain(
            'example.com',
            [
                'ns01.example.com',
                'ns02.example.com',
                'ns03.example.com',
                'ns04.example.com',
                'ns05.example.com',
                'ns06.example.com',
                'ns07.example.com',
                'ns08.example.com',
                'ns09.example.com',
                'ns10.example.com',
                'ns11.example.com',
                'ns12.example.com',
                'ns13.example.com',
                'ns14.example.com',
            ]
        );
    }

    /**
     * Test how a list_domain call is handled.
     */
    public function testInvalidKey(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidApiKeyResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(DynadotApiException::class);
        $api->setNameserversForDomain('example.com', ['ns1.example.com']);
    }

    /**
     * Test incorrect XML
     */
    public function testIncorrectXml(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidXmlResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(LibXMLException::class);
        $api->setNameserversForDomain('example.com', ['ns1.example.com']);
    }

    /**
     * Test unexpected XML
     */
    public function testUnexpectedXml(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('validXmlButWrongResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(DynadotApiException::class);
        $api->setNameserversForDomain('example.com', ['ns1.example.com']);
    }
}
