<?php

namespace Level23\Dynadot\ApiTests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\DynadotApi;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\DomainResponse\Domain;
use Sabre\Xml\LibXMLException;

class GetDomainListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test how a list_domain call is handled.
     */
    public function testListDomains()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/validListDomainResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $response = $api->getDomainList();

        $this->assertTrue(is_array($response));
        $this->assertContainsOnlyInstancesOf(Domain::class, $response);
    }

    /**
     * Test how a list_domain call is handled.
     */
    public function testListDomainsInvalidResponse()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/invalidListDomainResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->setExpectedException(DynadotApiException::class);
        $api->getDomainList();
    }

    /**
     * Test invalid key
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
        $api->getDomainList();
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
        $api->getDomainList();
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
        $api->getDomainList();
    }
}
