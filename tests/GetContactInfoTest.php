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
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

class GetContactInfoTest extends TestCase
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

    public function testGetContactInfoSuccess(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
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
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactInfo(12345);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals(12345, $result->contactId);
        $this->assertEquals('Example Corp', $result->organization);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john.doe@example.com', $result->email);
        $this->assertEquals('5551234567', $result->phoneNumber);
        $this->assertEquals('1', $result->phoneCc);
        $this->assertEquals('5551234568', $result->faxNumber);
        $this->assertEquals('1', $result->faxCc);
        $this->assertEquals('123 Main Street', $result->address1);
        $this->assertEquals('Suite 100', $result->address2);
        $this->assertEquals('New York', $result->city);
        $this->assertEquals('NY', $result->state);
        $this->assertEquals('10001', $result->zip);
        $this->assertEquals('US', $result->country);
    }

    public function testGetContactInfoWithMinimalData(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_id'   => 67890,
                'organization' => '',
                'name'         => 'Jane Smith',
                'email'        => 'jane.smith@example.com',
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
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactInfo(67890);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals(67890, $result->contactId);
        $this->assertEquals('', $result->organization);
        $this->assertEquals('Jane Smith', $result->name);
        $this->assertEquals('jane.smith@example.com', $result->email);
        $this->assertEquals('5559876543', $result->phoneNumber);
        $this->assertEquals('1', $result->phoneCc);
        $this->assertEquals('', $result->faxNumber);
        $this->assertEquals('', $result->faxCc);
        $this->assertEquals('456 Oak Avenue', $result->address1);
        $this->assertEquals('', $result->address2);
        $this->assertEquals('Los Angeles', $result->city);
        $this->assertEquals('CA', $result->state);
        $this->assertEquals('90210', $result->zip);
        $this->assertEquals('US', $result->country);
    }

    public function testGetContactInfoWithInternationalData(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_id'   => 11111,
                'organization' => 'International Ltd',
                'name'         => 'Pierre Dubois',
                'email'        => 'pierre.dubois@international.fr',
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
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactInfo(11111);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals(11111, $result->contactId);
        $this->assertEquals('International Ltd', $result->organization);
        $this->assertEquals('Pierre Dubois', $result->name);
        $this->assertEquals('pierre.dubois@international.fr', $result->email);
        $this->assertEquals('123456789', $result->phoneNumber);
        $this->assertEquals('33', $result->phoneCc);
        $this->assertEquals('123456788', $result->faxNumber);
        $this->assertEquals('33', $result->faxCc);
        $this->assertEquals('789 Rue de la Paix', $result->address1);
        $this->assertEquals('Étage 5', $result->address2);
        $this->assertEquals('Paris', $result->city);
        $this->assertEquals('Île-de-France', $result->state);
        $this->assertEquals('75001', $result->zip);
        $this->assertEquals('FR', $result->country);
    }

    public function testGetContactInfoContactNotFound(): void
    {
        $mockResponse = [
            'code'    => 404,
            'message' => 'Contact not found',
        ];

        $this->mockHandler->append(
            new Response(404, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Contact not found');

        $this->client->getContactInfo(99999);
    }

    public function testGetContactInfoApiError(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'Invalid contact ID format',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid contact ID format');

        $this->client->getContactInfo(0);
    }

    public function testGetContactInfoNetworkError(): void
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

        $this->client->getContactInfo(12345);
    }

    public function testGetContactInfoInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $this->client->getContactInfo(12345);
    }

    public function testGetContactInfoMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200,
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactInfo(12345);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertNull($result->contactId);
        $this->assertEquals('', $result->organization);
        $this->assertEquals('', $result->name);
        $this->assertEquals('', $result->email);
        $this->assertEquals('', $result->phoneNumber);
        $this->assertEquals('', $result->phoneCc);
        $this->assertEquals('', $result->faxNumber);
        $this->assertEquals('', $result->faxCc);
        $this->assertEquals('', $result->address1);
        $this->assertEquals('', $result->address2);
        $this->assertEquals('', $result->city);
        $this->assertEquals('', $result->state);
        $this->assertEquals('', $result->zip);
        $this->assertEquals('', $result->country);
    }

    public function testGetContactInfoWithNullContactId(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_id'   => null,
                'organization' => 'Test Organization',
                'name'         => 'Test Contact',
                'email'        => 'test@example.com',
                'phone_number' => '5551234567',
                'phone_cc'     => '1',
                'fax_number'   => '',
                'fax_cc'       => '',
                'address1'     => '123 Test Street',
                'address2'     => '',
                'city'         => 'Test City',
                'state'        => 'TS',
                'zip'          => '12345',
                'country'      => 'US',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactInfo(12345);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertNull($result->contactId);
        $this->assertEquals('Test Organization', $result->organization);
        $this->assertEquals('Test Contact', $result->name);
        $this->assertEquals('test@example.com', $result->email);
    }

    public function testGetContactInfoWithSpecialCharacters(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'contact_id'   => 22222,
                'organization' => 'Company & Sons, LLC',
                'name'         => 'José María García-López',
                'email'        => 'jose.maria@company-sons.com',
                'phone_number' => '555-123-4567',
                'phone_cc'     => '1',
                'fax_number'   => '555-123-4568',
                'fax_cc'       => '1',
                'address1'     => '123 Main St. #4B',
                'address2'     => 'Apt. 2C',
                'city'         => 'San José',
                'state'        => 'CA',
                'zip'          => '95123-4567',
                'country'      => 'US',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getContactInfo(22222);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals(22222, $result->contactId);
        $this->assertEquals('Company & Sons, LLC', $result->organization);
        $this->assertEquals('José María García-López', $result->name);
        $this->assertEquals('jose.maria@company-sons.com', $result->email);
        $this->assertEquals('555-123-4567', $result->phoneNumber);
        $this->assertEquals('1', $result->phoneCc);
        $this->assertEquals('555-123-4568', $result->faxNumber);
        $this->assertEquals('1', $result->faxCc);
        $this->assertEquals('123 Main St. #4B', $result->address1);
        $this->assertEquals('Apt. 2C', $result->address2);
        $this->assertEquals('San José', $result->city);
        $this->assertEquals('CA', $result->state);
        $this->assertEquals('95123-4567', $result->zip);
        $this->assertEquals('US', $result->country);
    }
}
