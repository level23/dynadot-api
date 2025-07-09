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
use Level23\Dynadot\Dto\DomainRegistrationRequest;
use Level23\Dynadot\Dto\DomainRegistrationResult;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

class RegisterDomainTest extends TestCase
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

    public function testRegisterDomainSuccess(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name'     => 'example.com',
                'expiration_date' => 1735689600,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com']
        );

        $result = $this->client->registerDomain('example.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('example.com', $result->domainName);
        $this->assertEquals(1735689600, $result->expirationDate);
    }

    public function testRegisterDomainWithContactIds(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name'     => 'test.com',
                'expiration_date' => 1735689600,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrationData = DomainRegistrationRequest::create(
            2,
            'AUTH456',
            67890,
            ['ns1.test.com', 'ns2.test.com'],
            'on',
            'USD',
            false,
            null,
            11111, // registrant contact ID
            22222, // admin contact ID
            33333, // tech contact ID
            44444  // billing contact ID
        );

        $result = $this->client->registerDomain('test.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('test.com', $result->domainName);
        $this->assertEquals(1735689600, $result->expirationDate);
    }

    public function testRegisterDomainWithContactObjects(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name'     => 'contact.com',
                'expiration_date' => 1735689600,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrantContact = Contact::create(
            'Registrant Corp',
            'John Registrant',
            'john@registrant.com',
            '5551234567',
            '1',
            '123 Registrant St',
            'Registrant City',
            'RC',
            '12345',
            'US'
        );

        $adminContact = Contact::create(
            'Admin Corp',
            'Jane Admin',
            'jane@admin.com',
            '5559876543',
            '1',
            '456 Admin St',
            'Admin City',
            'AC',
            '54321',
            'US'
        );

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH789',
            99999,
            ['ns1.contact.com', 'ns2.contact.com'],
            'off',
            'USD',
            false,
            null,
            null, // registrant contact ID
            null, // admin contact ID
            null, // tech contact ID
            null, // billing contact ID
            $registrantContact,
            $adminContact,
            null, // tech contact
            null  // billing contact
        );

        $result = $this->client->registerDomain('contact.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('contact.com', $result->domainName);
        $this->assertEquals(1735689600, $result->expirationDate);
    }

    public function testRegisterDomainWithPremium(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name'     => 'premium.com',
                'expiration_date' => 1735689600,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH999',
            11111,
            ['ns1.premium.com', 'ns2.premium.com'],
            'on',
            'USD',
            true, // register premium
            'PREMIUM10' // coupon code
        );

        $result = $this->client->registerDomain('premium.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('premium.com', $result->domainName);
        $this->assertEquals(1735689600, $result->expirationDate);
    }

    public function testRegisterDomainWithDifferentCurrency(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name'     => 'eur.com',
                'expiration_date' => 1735689600,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH777',
            55555,
            ['ns1.eur.com', 'ns2.eur.com'],
            'on',
            'EUR',
            false
        );

        $result = $this->client->registerDomain('eur.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('eur.com', $result->domainName);
        $this->assertEquals(1735689600, $result->expirationDate);
    }

    public function testRegisterDomainDomainNotAvailable(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'Domain is not available for registration',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Domain is not available for registration');

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com']
        );

        $this->client->registerDomain('taken.com', $registrationData);
    }

    public function testRegisterDomainInvalidAuthCode(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'Invalid authorization code',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid authorization code');

        $registrationData = DomainRegistrationRequest::create(
            1,
            'INVALID_AUTH',
            12345,
            ['ns1.example.com', 'ns2.example.com']
        );

        $this->client->registerDomain('example.com', $registrationData);
    }

    public function testRegisterDomainInvalidCustomerId(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'Invalid customer ID',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid customer ID');

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            0, // invalid customer ID
            ['ns1.example.com', 'ns2.example.com']
        );

        $this->client->registerDomain('example.com', $registrationData);
    }

    public function testRegisterDomainNetworkError(): void
    {
        // Create a RequestException with no response to simulate a network error
        $this->mockHandler->append(
            new RequestException(
                'Network error',
                new Request('POST', 'test'),
                null // No response indicates a network error
            )
        );

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('No response received from Dynadot API');

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com']
        );

        $this->client->registerDomain('example.com', $registrationData);
    }

    public function testRegisterDomainInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com']
        );

        $this->client->registerDomain('example.com', $registrationData);
    }

    public function testRegisterDomainMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200,
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com']
        );

        $result = $this->client->registerDomain('example.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('', $result->domainName);
        $this->assertEquals(0, $result->expirationDate);
    }

    public function testRegisterDomainWithSpecialCharacters(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name'     => 'example-domain.com',
                'expiration_date' => 1735689600,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example-domain.com', 'ns2.example-domain.com']
        );

        $result = $this->client->registerDomain('example-domain.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('example-domain.com', $result->domainName);
        $this->assertEquals(1735689600, $result->expirationDate);
    }

    public function testRegisterDomainWithLongDuration(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name'     => 'long.com',
                'expiration_date' => 1735689600,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $registrationData = DomainRegistrationRequest::create(
            10, // 10 years
            'AUTH123',
            12345,
            ['ns1.long.com', 'ns2.long.com']
        );

        $result = $this->client->registerDomain('long.com', $registrationData);

        $this->assertInstanceOf(DomainRegistrationResult::class, $result);
        $this->assertEquals('long.com', $result->domainName);
        $this->assertEquals(1735689600, $result->expirationDate);
    }
}
