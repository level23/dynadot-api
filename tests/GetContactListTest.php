<?php

namespace Level23\Dynadot\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\Contact;
use Level23\Dynadot\Dto\ContactListResult;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

class GetContactListTest extends TestCase
{
    private Client $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack      = HandlerStack::create($this->mockHandler);

        $this->client = new Client('test-api-key', 'test-api-secret');

        // Use reflection to replace the Guzzle client with our mock
        $reflection   = new \ReflectionClass($this->client);
        $httpProperty = $reflection->getProperty('http');
        $httpProperty->setAccessible(true);

        $mockGuzzleClient = new GuzzleClient([
            'base_uri' => 'https://api.dynadot.com/restful/v1/',
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'handler' => $handlerStack,
        ]);

        $httpProperty->setValue($this->client, $mockGuzzleClient);
    }

    public function testGetContactListSuccess(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_list' => [
                    [
                        'contact_id'   => 12345,
                        'organization' => 'Example Corp',
                        'name'         => 'John Doe',
                        'email'        => 'john.doe@example.com',
                        'phone_number' => '5551234567',
                        'phone_cc'     => '1',
                        'fax_number'   => '5551234568',
                        'fax_cc'       => '1',
                        'address1'     => '123 Main Street',
                        'address2'     => 'Suite 100',
                        'city'         => 'New York',
                        'state'        => 'NY',
                        'zip'          => '10001',
                        'country'      => 'US',
                    ],
                    [
                        'contact_id'   => 67890,
                        'organization' => 'Another Company',
                        'name'         => 'Jane Smith',
                        'email'        => 'jane.smith@another.com',
                        'phone_number' => '5559876543',
                        'phone_cc'     => '1',
                        'fax_number'   => '',
                        'fax_cc'       => '',
                        'address1'     => '456 Oak Avenue',
                        'address2'     => '',
                        'city'         => 'Los Angeles',
                        'state'        => 'CA',
                        'zip'          => '90210',
                        'country'      => 'US',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactList();

        $this->assertInstanceOf(ContactListResult::class, $result);
        $this->assertCount(2, $result->contacts);

        // Test first contact
        $firstContact = $result->contacts[0];
        $this->assertInstanceOf(Contact::class, $firstContact);
        $this->assertEquals(12345, $firstContact->contactId);
        $this->assertEquals('Example Corp', $firstContact->organization);
        $this->assertEquals('John Doe', $firstContact->name);
        $this->assertEquals('john.doe@example.com', $firstContact->email);

        // Test second contact
        $secondContact = $result->contacts[1];
        $this->assertInstanceOf(Contact::class, $secondContact);
        $this->assertEquals(67890, $secondContact->contactId);
        $this->assertEquals('Another Company', $secondContact->organization);
        $this->assertEquals('Jane Smith', $secondContact->name);
        $this->assertEquals('jane.smith@another.com', $secondContact->email);
    }

    public function testGetContactListEmpty(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_list' => [],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactList();

        $this->assertInstanceOf(ContactListResult::class, $result);
        $this->assertCount(0, $result->contacts);
    }

    public function testGetContactListSingleContact(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_list' => [
                    [
                        'contact_id'   => 11111,
                        'organization' => 'Single Contact Corp',
                        'name'         => 'Single Contact',
                        'email'        => 'single@contact.com',
                        'phone_number' => '5551111111',
                        'phone_cc'     => '1',
                        'fax_number'   => '',
                        'fax_cc'       => '',
                        'address1'     => '111 Single Street',
                        'address2'     => '',
                        'city'         => 'Single City',
                        'state'        => 'SC',
                        'zip'          => '11111',
                        'country'      => 'US',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactList();

        $this->assertInstanceOf(ContactListResult::class, $result);
        $this->assertCount(1, $result->contacts);

        $contact = $result->contacts[0];
        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertEquals(11111, $contact->contactId);
        $this->assertEquals('Single Contact Corp', $contact->organization);
        $this->assertEquals('Single Contact', $contact->name);
        $this->assertEquals('single@contact.com', $contact->email);
    }

    public function testGetContactListApiError(): void
    {
        $mockResponse = [
            'code'    => 401,
            'message' => 'Unauthorized access',
        ];

        $this->mockHandler->append(
            new Response(401, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Unauthorized access');

        $this->client->getContactList();
    }

    public function testGetContactListNetworkError(): void
    {
        // Create a RequestException with no response to simulate a network error
        $this->mockHandler->append(
            new RequestException(
                'Network error',
                new Request('GET', 'test'),
                null // No response indicates a network error
            )
        );

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('No response received from Dynadot API');

        $this->client->getContactList();
    }

    public function testGetContactListInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $this->client->getContactList();
    }

    public function testGetContactListMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200,
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactList();

        $this->assertInstanceOf(ContactListResult::class, $result);
        $this->assertCount(0, $result->contacts);
    }

    public function testGetContactListMissingContactListKey(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                // Missing 'contact_list' key
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactList();

        $this->assertInstanceOf(ContactListResult::class, $result);
        $this->assertCount(0, $result->contacts);
    }

    public function testGetContactListWithInternationalContacts(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_list' => [
                    [
                        'contact_id'   => 22222,
                        'organization' => 'International Ltd',
                        'name'         => 'Pierre Dubois',
                        'email'        => 'pierre@international.fr',
                        'phone_number' => '123456789',
                        'phone_cc'     => '33',
                        'fax_number'   => '123456788',
                        'fax_cc'       => '33',
                        'address1'     => '789 Rue de la Paix',
                        'address2'     => 'Étage 5',
                        'city'         => 'Paris',
                        'state'        => 'Île-de-France',
                        'zip'          => '75001',
                        'country'      => 'FR',
                    ],
                    [
                        'contact_id'   => 33333,
                        'organization' => 'Deutsche AG',
                        'name'         => 'Hans Müller',
                        'email'        => 'hans@deutsche.de',
                        'phone_number' => '987654321',
                        'phone_cc'     => '49',
                        'fax_number'   => '987654320',
                        'fax_cc'       => '49',
                        'address1'     => '456 Hauptstraße',
                        'address2'     => 'Etage 3',
                        'city'         => 'Berlin',
                        'state'        => 'Berlin',
                        'zip'          => '10115',
                        'country'      => 'DE',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactList();

        $this->assertInstanceOf(ContactListResult::class, $result);
        $this->assertCount(2, $result->contacts);

        // Test French contact
        $frenchContact = $result->contacts[0];
        $this->assertEquals(22222, $frenchContact->contactId);
        $this->assertEquals('International Ltd', $frenchContact->organization);
        $this->assertEquals('Pierre Dubois', $frenchContact->name);
        $this->assertEquals('33', $frenchContact->phoneCc);
        $this->assertEquals('FR', $frenchContact->country);

        // Test German contact
        $germanContact = $result->contacts[1];
        $this->assertEquals(33333, $germanContact->contactId);
        $this->assertEquals('Deutsche AG', $germanContact->organization);
        $this->assertEquals('Hans Müller', $germanContact->name);
        $this->assertEquals('49', $germanContact->phoneCc);
        $this->assertEquals('DE', $germanContact->country);
    }
}
