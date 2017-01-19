<?php

namespace Level23\Dynadot\ApiTests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\DynadotApi;
use Level23\Dynadot\Exception\ApiLimitationExceededException;
use Level23\Dynadot\Exception\DynadotApiException;
use Sabre\Xml\LibXMLException;

class SetNameserversTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test setting nameservers for a domain.
     */
    public function testSetNameservers()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/validSetNsResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $result = $api->setNameserversForDomain('example.com', ['ns01.example.com', 'ns02.example.com']);
        $this->assertEquals($result, null);
    }

    /**
     * Test setting nameservers for a domain.
     */
    public function testInvalidResponse()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/invalidSetNsResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->setExpectedException(DynadotApiException::class);
        $api->setNameserversForDomain('example.com', ['ns01.example.com', 'ns02.example.com']);
    }

    /**
     * Test if some limitations the API has for setNs calls are respected and the proper exceptions are thrown.
     */
    public function testApiLimitations()
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
            )
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        // we are going to expect a ApiLimitationExceededException if we try to set 14 nameservers
        $this->setExpectedException(ApiLimitationExceededException::class);

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
    public function testInvalidKey()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/invalidApiKeyResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->setExpectedException(DynadotApiException::class);
        $api->setNameserversForDomain('example.com', ['ns1.example.com']);
    }

    /**
     * Test incorrect XML
     */
    public function testIncorrectXml()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/invalidXmlResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->setExpectedException(LibXMLException::class);
        $api->setNameserversForDomain('example.com', ['ns1.example.com']);
    }

    /**
     * Test unexpected XML
     */
    public function testUnexpectedXml()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/validXmlButWrongResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->setExpectedException(DynadotApiException::class);
        $api->setNameserversForDomain('example.com', ['ns1.example.com']);
    }
}
