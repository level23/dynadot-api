<?php

namespace Level23\Dynadot\Tests\ApiTests;

use GuzzleHttp\Psr7\Response;
use Sabre\Xml\LibXMLException;
use Level23\Dynadot\DynadotApi;
use GuzzleHttp\Handler\MockHandler;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\DomainResponse\Domain;

class GetDomainListTest extends TestCase
{
    /**
     * Test how a list_domain call is handled.
     */
    public function testListDomains(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('validListDomainResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $response = $api->getDomainList();

        $this->assertTrue(is_array($response));
        $this->assertContainsOnlyInstancesOf(Domain::class, $response);
    }

    /**
     * Test how a list_domain call is handled.
     */
    public function testListDomainsInvalidResponse(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidListDomainResponse.txt');


        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(DynadotApiException::class);
        $api->getDomainList();
    }

    /**
     * Test invalid key
     */
    public function testInvalidKey(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidApiKeyResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);
        $this->expectException(DynadotApiException::class);
        $api->getDomainList();
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
        $api->getDomainList();
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
        $api->getDomainList();
    }
}
