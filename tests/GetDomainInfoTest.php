<?php

namespace Level23\Dynadot\Tests;

use PHPUnit\Framework\TestCase;
use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\DomainListResult;
use Level23\Dynadot\Dto\DomainInfo;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class GetDomainInfoTest extends TestCase
{
    private Client $client;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        
        $this->client = new Client('test-api-key', 'test-api-secret');
        
        // Use reflection to replace the Guzzle client with our mock
        $reflection = new \ReflectionClass($this->client);
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

    public function testGetDomainInfoSuccess(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domainInfo' => [
                    [
                        'domainName' => 'example.com',
                        'expiration' => 1735689600,
                        'registration' => 1640995200,
                        'glueInfo' => [],
                        'registrant_contactId' => 12345,
                        'admin_contactId' => 12346,
                        'tech_contactId' => 12347,
                        'billing_contactId' => 12348,
                        'locked' => false,
                        'disabled' => false,
                        'udrpLocked' => false,
                        'registrant_unverified' => false,
                        'hold' => false,
                        'privacy' => 'enabled',
                        'is_for_sale' => false,
                        'renew_option' => 'auto',
                        'note' => 'Test domain',
                        'folder_id' => 1,
                        'folder_name' => 'Default',
                        'status' => 'active'
                    ]
                ]
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getDomainInfo('example.com');

        $this->assertInstanceOf(DomainListResult::class, $result);
        $this->assertCount(1, $result->domains);
        
        $domain = $result->domains[0];
        $this->assertInstanceOf(DomainInfo::class, $domain);
        $this->assertEquals('example.com', $domain->domainName);
        $this->assertEquals(1735689600, $domain->expiration);
        $this->assertEquals(1640995200, $domain->registration);
        $this->assertEquals(12345, $domain->registrantContactId);
        $this->assertEquals(12346, $domain->adminContactId);
        $this->assertEquals(12347, $domain->techContactId);
        $this->assertEquals(12348, $domain->billingContactId);
        $this->assertFalse($domain->locked);
        $this->assertFalse($domain->disabled);
        $this->assertFalse($domain->udrpLocked);
        $this->assertFalse($domain->registrantUnverified);
        $this->assertFalse($domain->hold);
        $this->assertEquals('enabled', $domain->privacy);
        $this->assertFalse($domain->isForSale);
        $this->assertEquals('auto', $domain->renewOption);
        $this->assertEquals('Test domain', $domain->note);
        $this->assertEquals(1, $domain->folderId);
        $this->assertEquals('Default', $domain->folderName);
        $this->assertEquals('active', $domain->status);
    }

    public function testGetDomainInfoEmptyResponse(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domainInfo' => []
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getDomainInfo('nonexistent.com');

        $this->assertInstanceOf(DomainListResult::class, $result);
        $this->assertCount(0, $result->domains);
    }

    public function testGetDomainInfoApiError(): void
    {
        $mockResponse = [
            'code' => 404,
            'message' => 'Domain not found'
        ];

        $this->mockHandler->append(
            new Response(404, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Domain not found');

        $this->client->getDomainInfo('nonexistent.com');
    }

    public function testGetDomainInfoNetworkError(): void
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

        $this->client->getDomainInfo('example.com');
    }

    public function testGetDomainInfoInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $this->client->getDomainInfo('example.com');
    }

    public function testGetDomainInfoMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getDomainInfo('example.com');

        $this->assertInstanceOf(DomainListResult::class, $result);
        $this->assertCount(0, $result->domains);
    }

    public function testGetDomainInfoMultipleDomains(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domainInfo' => [
                    [
                        'domainName' => 'example1.com',
                        'expiration' => 1735689600,
                        'registration' => 1640995200,
                        'glueInfo' => [],
                        'registrant_contactId' => 12345,
                        'admin_contactId' => 12346,
                        'tech_contactId' => 12347,
                        'billing_contactId' => 12348,
                        'locked' => false,
                        'disabled' => false,
                        'udrpLocked' => false,
                        'registrant_unverified' => false,
                        'hold' => false,
                        'privacy' => 'enabled',
                        'is_for_sale' => false,
                        'renew_option' => 'auto',
                        'note' => null,
                        'folder_id' => 1,
                        'folder_name' => 'Default',
                        'status' => 'active'
                    ],
                    [
                        'domainName' => 'example2.com',
                        'expiration' => 1735776000,
                        'registration' => 1641081600, 
                        'glueInfo' => [],
                        'registrant_contactId' => 12349,
                        'admin_contactId' => 12350,
                        'tech_contactId' => 12351,
                        'billing_contactId' => 12352,
                        'locked' => true,
                        'disabled' => false,
                        'udrpLocked' => false,
                        'registrant_unverified' => false,
                        'hold' => false,
                        'privacy' => 'disabled',
                        'is_for_sale' => true,
                        'renew_option' => 'manual',
                        'note' => 'For sale domain',
                        'folder_id' => 2,
                        'folder_name' => 'For Sale',
                        'status' => 'active'
                    ]
                ]
            ]
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getDomainInfo('example.com');

        $this->assertInstanceOf(DomainListResult::class, $result);
        $this->assertCount(2, $result->domains);
        
        $domain1 = $result->domains[0];
        $this->assertEquals('example1.com', $domain1->domainName);
        $this->assertFalse($domain1->locked);
        $this->assertEquals('enabled', $domain1->privacy);
        $this->assertFalse($domain1->isForSale);
        $this->assertEquals('auto', $domain1->renewOption);
        $this->assertNull($domain1->note);
        
        $domain2 = $result->domains[1];
        $this->assertEquals('example2.com', $domain2->domainName);
        $this->assertTrue($domain2->locked);
        $this->assertEquals('disabled', $domain2->privacy);
        $this->assertTrue($domain2->isForSale);
        $this->assertEquals('manual', $domain2->renewOption);
        $this->assertEquals('For sale domain', $domain2->note);
    }
} 