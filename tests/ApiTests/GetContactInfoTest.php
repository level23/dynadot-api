<?php

namespace Level23\Dynadot\Tests\ApiTests;

use GuzzleHttp\Psr7\Response;
use Sabre\Xml\LibXMLException;
use Level23\Dynadot\DynadotApi;
use GuzzleHttp\Handler\MockHandler;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\GetContactResponse\Contact;

class GetContactInfoTest extends TestCase
{
    /**
     * Test how a get_contact call is handled.
     */
    public function testValidResponse()
    {
        // set up mock objects
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('validGetContactResponse.txt');

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

        $mockHandler = $this->getMockedResponse('invalidGetContactResponse.txt');
        $api->setGuzzleOptions(['handler' => $mockHandler]);

        // do a request
        $this->expectException(DynadotApiException::class);
        $api->getContactInfo(12345);
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
        $api->getContactInfo(12345);
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
        $api->getContactInfo(12345);
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
        $api->getContactInfo(12345);
    }
}
