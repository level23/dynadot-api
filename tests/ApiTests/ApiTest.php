<?php

namespace Level23\Dynadot\ApiTests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\DynadotApi;

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

        $this->setExpectedException(\Level23\Dynadot\Exception\ApiHttpCallFailedException::class);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $response = $api->performDomainInfo('example.com');
    }

    /**
     * Test how a domain_info response for an invalid domain that is not owned by our account is handled.
     */
    public function testPerformDomainInfoForInvalidDomain()
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

        // in this case, we pretend example.com isn't owned by us
        $response = $api->performDomainInfo('example.com');
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\DomainInfoResponseHeader::class,
            $response['{}DomainInfoResponseHeader']
        );
        $this->assertEquals(-1, $response['{}DomainInfoResponseHeader']->SuccessCode);
    }

    /**
     * Test how a domain_info response for an valid domain that is owned by our account is handled.
     */
    public function testPerformDomainInfoForValidDomain()
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

        $response = $api->performDomainInfo('example.com');
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\DomainInfoResponseHeader::class,
            $response['{}DomainInfoResponseHeader']
        );

        // did we get the expected response header
        $this->assertEquals(0, $response['{}DomainInfoResponseHeader']->SuccessCode);

        // did we get a domain back?
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain::class,
            $response['{}DomainInfoContent']['{}Domain']
        );

        // did we get the expected domain name back in the response?
        $this->assertEquals('example.com', $response['{}DomainInfoContent']['{}Domain']->Name);

        // check if we got whois info too
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Whois::class,
            $response['{}DomainInfoContent']['{}Domain']->Whois
        );

        $whoisResponse = $response['{}DomainInfoContent']['{}Domain']->Whois;

        // check instances of whois response
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Registrant::class,
            $whoisResponse->Registrant
        );
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Admin::class,
            $whoisResponse->Admin
        );
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Technical::class,
            $whoisResponse->Technical
        );
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Billing::class,
            $whoisResponse->Billing
        );

        // check IDs reported
        $this->assertEquals(
            1301,
            $whoisResponse->Registrant->ContactId
        );
        $this->assertEquals(
            1302,
            $whoisResponse->Admin->ContactId
        );
        $this->assertEquals(
            1303,
            $whoisResponse->Technical->ContactId
        );
        $this->assertEquals(
            1304,
            $whoisResponse->Billing->ContactId
        );

    }

    /**
     * Test how a list_domain call is handled.
     */
    public function testPerformListDomain()
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
        $response = $api->performListDomain();

        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\ListDomainInfoHeader::class,
            $response['{}ListDomainInfoHeader']
        );

        // check if we got a valid status code
        $this->assertEquals(0, $response['{}ListDomainInfoHeader']->StatusCode);

        // check if we got two Domain objects in the DomainInfoList
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Domain::class,
            $response['{}ListDomainInfoContent']['{}DomainInfoList'][0]['{}Domain']
        );
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Domain::class,
            $response['{}ListDomainInfoContent']['{}DomainInfoList'][1]['{}Domain']
        );

        // check if the domain names were parsed properly
        $this->assertEquals(
            'domain-exp140.com',
            $response['{}ListDomainInfoContent']['{}DomainInfoList'][0]['{}Domain']->Name
        );
        $this->assertEquals(
            'domain-exp141.com',
            $response['{}ListDomainInfoContent']['{}DomainInfoList'][1]['{}Domain']->Name
        );

        // check if we got whois info too
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Whois::class,
            $response['{}ListDomainInfoContent']['{}DomainInfoList'][0]['{}Domain']->Whois
        );

        $whoisResponse = $response['{}ListDomainInfoContent']['{}DomainInfoList'][0]['{}Domain']->Whois;

        // check instances of whois response
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Registrant::class,
            $whoisResponse->Registrant
        );
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Admin::class,
            $whoisResponse->Admin
        );
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Technical::class,
            $whoisResponse->Technical
        );
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Billing::class,
            $whoisResponse->Billing
        );

        // check IDs reported
        $this->assertEquals(
            0,
            $whoisResponse->Registrant->ContactId
        );
        $this->assertEquals(
            0,
            $whoisResponse->Admin->ContactId
        );
        $this->assertEquals(
            0,
            $whoisResponse->Technical->ContactId
        );
        $this->assertEquals(
            0,
            $whoisResponse->Billing->ContactId
        );
    }

    /**
     * Test setting nameservers for a domain.
     */
    public function testPerformSetNs()
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

        $response = $api->performSetNs('example.com', array('ns01.example.com', 'ns02.example.com'));
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\SetNsResponses\SetNsHeader::class,
            $response['{}SetNsHeader']
        );
        $this->assertEquals(0, $response['{}SetNsHeader']->StatusCode);
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
        $this->setExpectedException(\Level23\Dynadot\Exception\ApiLimitationExceededException::class);

        // try to set 14 nameservers for example.com
        $api->performSetNs(
            'example.com',
            array(
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
            )
        );
    }

    /**
     * Test how a get_contact call is handled.
     */
    public function testPerformGetContact()
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
        $response = $api->performGetContact(12345);

        // check if we got a header
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\GetContactResponses\GetContactHeader::class,
            $response['{}GetContactHeader']
        );

        // check if the status code was okay
        $this->assertEquals(0, $response['{}GetContactHeader']->StatusCode);

        // test if we got a Contact object
        $this->assertInstanceOf(
            \Level23\Dynadot\ResultObjects\GetContactResponses\Contact::class,
            $response['{}GetContactContent']['{}Contact']
        );

        // test if the Contact was parsed properly
        $this->assertEquals('12345', $response['{}GetContactContent']['{}Contact']->ContactId);
        $this->assertEquals('name', $response['{}GetContactContent']['{}Contact']->Name);
        $this->assertEquals('example@example.com', $response['{}GetContactContent']['{}Contact']->Email);
        $this->assertEquals('0', $response['{}GetContactContent']['{}Contact']->PhoneCc);
        $this->assertEquals('phone number', $response['{}GetContactContent']['{}Contact']->PhoneNum);
        $this->assertEquals('example faxcc', $response['{}GetContactContent']['{}Contact']->FaxCc);
        $this->assertEquals('example faxnum', $response['{}GetContactContent']['{}Contact']->FaxNum);
        $this->assertEquals('address1', $response['{}GetContactContent']['{}Contact']->Address1);
        $this->assertEquals('address2', $response['{}GetContactContent']['{}Contact']->Address2);
        $this->assertEquals('city', $response['{}GetContactContent']['{}Contact']->City);
        $this->assertEquals('state', $response['{}GetContactContent']['{}Contact']->State);
        $this->assertEquals('zipcode', $response['{}GetContactContent']['{}Contact']->ZipCode);
        $this->assertEquals('country', $response['{}GetContactContent']['{}Contact']->Country);
    }
}
