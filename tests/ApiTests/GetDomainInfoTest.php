<?php

namespace Level23\Dynadot\Tests\ApiTests;

use GuzzleHttp\Psr7\Response;
use Sabre\Xml\LibXMLException;
use Level23\Dynadot\DynadotApi;
use GuzzleHttp\Handler\MockHandler;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\DomainResponse\Domain;

class GetDomainInfoTest extends TestCase
{
    /**
     * Test how a domain_info response for an invalid domain that is not owned by our account is handled.
     */
    public function testInvalidDomain()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidDomainInfoResponseBody.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->expectException(DynadotApiException::class);

        // in this case, we pretend example.com isn't owned by us
        $api->getDomainInfo('example.com');
    }

    /**
     * Test how a domain_info response for an valid domain that is owned by our account is handled.
     */
    public function testValidDomain()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('validDomainInfoResponseBody.txt');
        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $response = $api->getDomainInfo('example.com');
        $this->assertInstanceOf(Domain::class, $response);
    }

    /**
     * Test invalid key
     */
    public function testInvalidKey()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidApiKeyResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(DynadotApiException::class);
        $api->getDomainInfo('example.com');
    }

    /**
     * Test incorrect XML
     */
    public function testIncorrectXml()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidXmlResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(LibXMLException::class);
        $api->getDomainInfo('example.com');
    }

    /**
     * Test unexpected XML
     */
    public function testUnexpectedXml()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('validXmlButWrongResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(DynadotApiException::class);
        $api->getDomainInfo('example.com');
    }
}
