<?php

namespace Level23\Dynadot\ApiTests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\DynadotApi;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\DomainResponse\Domain;
use Level23\Dynadot\ResultObjects\GetContactResponse\Contact;
use Sabre\Xml\LibXMLException;

class GetContactInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test how a get_contact call is handled.
     */
    public function testValidResponse()
    {
        // set up mock objects
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/validGetContactResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        // do a request
        $response = $api->getContactInfo(12345);

        // check if we got a header
        $this->assertInstanceOf(Contact::class, $response);


        // test if the Contact was parsed properly
        $this->assertEquals('12345', $response->ContactId);
        $this->assertEquals('name', $response->Name);
        $this->assertEquals('example@example.com', $response->Email);
        $this->assertEquals('0', $response->PhoneCc);
        $this->assertEquals('phone number', $response->PhoneNum);
        $this->assertEquals('example faxcc', $response->FaxCc);
        $this->assertEquals('example faxnum', $response->FaxNum);
        $this->assertEquals('address1', $response->Address1);
        $this->assertEquals('address2', $response->Address2);
        $this->assertEquals('city', $response->City);
        $this->assertEquals('state', $response->State);
        $this->assertEquals('zipcode', $response->ZipCode);
        $this->assertEquals('country', $response->Country);
    }


    /**
     * Test invalid contact response
     */
    public function testInvalidResponse()
    {
        // set up mock objects
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/invalidGetContactResponse.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        // do a request
        $this->setExpectedException(DynadotApiException::class);
        $api->getContactInfo(12345);
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
        $api->getContactInfo(12345);
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
        $api->getContactInfo(12345);
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
        $api->getContactInfo(12345);
    }
}
