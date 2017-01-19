<?php

namespace Level23\Dynadot\ApiTests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\DynadotApi;
use Level23\Dynadot\Exception\ApiHttpCallFailedException;
use Level23\Dynadot\Exception\ApiLimitationExceededException;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\DomainResponse\Domain;
use Level23\Dynadot\ResultObjects\GetContactResponse\Contact;

class ApiTests extends \PHPUnit_Framework_TestCase
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

    /**
     * Test how a domain_info response for an invalid domain that is not owned by our account is handled.
     */
    public function testGetDomainInfoForInvalidDomain()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/invalidDomainInfoResponseBody.txt'
                )
            )
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->setExpectedException(DynadotApiException::class);

        // in this case, we pretend example.com isn't owned by us
        $response = $api->getDomainInfo('example.com');
    }

    /**
     * Test how a domain_info response for an valid domain that is owned by our account is handled.
     */
    public function testGetDomainInfoForValidDomain()
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = new MockHandler([
            new Response(
                200,
                [],
                file_get_contents(
                    dirname(__FILE__) . DIRECTORY_SEPARATOR .
                    'MockHttpResponses/validDomainInfoResponseBody.txt'
                )
            ),
        ]);

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $response = $api->getDomainInfo('example.com');
        $this->assertInstanceOf(Domain::class, $response);
    }

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
    public function testListDomainInvalidKey()
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
     * Test how a list_domain call is handled.
     */
    public function testGetContactInvalidKey()
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
        $api->getContactInfo(1234);
    }

    /**
     * Test how a list_domain call is handled.
     */
    public function testGetDomainInfoInvalidKey()
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
        $api->getDomainInfo('example.com');
    }

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
    public function testSetNameserversInvalidResponse()
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
    public function testSetNsApiLimitationsHandling()
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
    public function testSetNameserversInvalidKey()
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
     * Test how a get_contact call is handled.
     */
    public function testGetContact()
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
}
