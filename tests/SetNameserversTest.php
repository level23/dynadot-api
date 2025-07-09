<?php

namespace Level23\Dynadot\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\NameserverUpdateResult;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

class SetNameserversTest extends TestCase
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

    public function testSetNameserversSuccess(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Nameservers updated successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $nameservers = ['ns1.example.com', 'ns2.example.com'];
        $result      = $this->client->setNameservers('example.com', $nameservers);

        $this->assertInstanceOf(NameserverUpdateResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Nameservers updated successfully', $result->message);
    }

    public function testSetNameserversWithSingleNameserver(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Nameserver updated successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $nameservers = ['ns1.example.com'];
        $result      = $this->client->setNameservers('example.com', $nameservers);

        $this->assertInstanceOf(NameserverUpdateResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Nameserver updated successfully', $result->message);
    }

    public function testSetNameserversWithMultipleNameservers(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Nameservers updated successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $nameservers = ['ns1.example.com', 'ns2.example.com', 'ns3.example.com', 'ns4.example.com'];
        $result      = $this->client->setNameservers('example.com', $nameservers);

        $this->assertInstanceOf(NameserverUpdateResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Nameservers updated successfully', $result->message);
    }

    public function testSetNameserversApiError(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'Invalid nameserver format',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid nameserver format');

        $nameservers = ['invalid-nameserver'];
        $this->client->setNameservers('example.com', $nameservers);
    }

    public function testSetNameserversDomainNotFound(): void
    {
        $mockResponse = [
            'code'    => 404,
            'message' => 'Domain not found',
        ];

        $this->mockHandler->append(
            new Response(404, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Domain not found');

        $nameservers = ['ns1.example.com', 'ns2.example.com'];
        $this->client->setNameservers('nonexistent.com', $nameservers);
    }

    public function testSetNameserversNetworkError(): void
    {
        // Create a RequestException with no response to simulate a network error
        $this->mockHandler->append(
            new RequestException(
                'Network error',
                new Request('PUT', 'test'),
                null // No response indicates a network error
            )
        );

        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('No response received from Dynadot API');

        $nameservers = ['ns1.example.com', 'ns2.example.com'];
        $this->client->setNameservers('example.com', $nameservers);
    }

    public function testSetNameserversEmptyNameserversArray(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'At least one nameserver is required',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('At least one nameserver is required');

        $nameservers = [];
        $this->client->setNameservers('example.com', $nameservers);
    }

    public function testSetNameserversInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $nameservers = ['ns1.example.com', 'ns2.example.com'];
        $this->client->setNameservers('example.com', $nameservers);
    }

    public function testSetNameserversMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200,
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $nameservers = ['ns1.example.com', 'ns2.example.com'];
        $result      = $this->client->setNameservers('example.com', $nameservers);

        $this->assertInstanceOf(NameserverUpdateResult::class, $result);
        $this->assertEquals(0, $result->code);
        $this->assertEquals('', $result->message);
    }

    public function testSetNameserversWithSpecialCharacters(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Nameservers updated successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $nameservers = ['ns1.example-domain.com', 'ns2.sub.example.com'];
        $result      = $this->client->setNameservers('example.com', $nameservers);

        $this->assertInstanceOf(NameserverUpdateResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Nameservers updated successfully', $result->message);
    }
}
