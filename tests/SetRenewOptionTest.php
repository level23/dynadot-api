<?php

namespace Level23\Dynadot\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\RenewOptionResult;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

class SetRenewOptionTest extends TestCase
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

    public function testSetRenewOptionAuto(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Renew option set to auto successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->setRenewOption('example.com', 'auto');

        $this->assertInstanceOf(RenewOptionResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Renew option set to auto successfully', $result->message);
    }

    public function testSetRenewOptionManual(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Renew option set to manual successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->setRenewOption('example.com', 'manual');

        $this->assertInstanceOf(RenewOptionResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Renew option set to manual successfully', $result->message);
    }

    public function testSetRenewOptionOff(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Renew option set to off successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->setRenewOption('example.com', 'off');

        $this->assertInstanceOf(RenewOptionResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Renew option set to off successfully', $result->message);
    }

    public function testSetRenewOptionDomainNotFound(): void
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

        $this->client->setRenewOption('nonexistent.com', 'auto');
    }

    public function testSetRenewOptionInvalidOption(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'Invalid renew option',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid renew option');

        $this->client->setRenewOption('example.com', 'invalid_option');
    }

    public function testSetRenewOptionApiError(): void
    {
        $mockResponse = [
            'code'    => 500,
            'message' => 'Internal server error',
        ];

        $this->mockHandler->append(
            new Response(500, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Internal server error');

        $this->client->setRenewOption('example.com', 'auto');
    }

    public function testSetRenewOptionNetworkError(): void
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

        $this->client->setRenewOption('example.com', 'auto');
    }

    public function testSetRenewOptionInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $this->client->setRenewOption('example.com', 'auto');
    }

    public function testSetRenewOptionMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200,
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->setRenewOption('example.com', 'auto');

        $this->assertInstanceOf(RenewOptionResult::class, $result);
        $this->assertNull($result->code);
        $this->assertNull($result->message);
    }

    public function testSetRenewOptionWithSpecialCharacters(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Renew option updated for example-domain.com',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->setRenewOption('example-domain.com', 'auto');

        $this->assertInstanceOf(RenewOptionResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Renew option updated for example-domain.com', $result->message);
    }

    public function testSetRenewOptionWithSubdomain(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'code'    => 200,
                'message' => 'Renew option set successfully',
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->setRenewOption('sub.example.com', 'manual');

        $this->assertInstanceOf(RenewOptionResult::class, $result);
        $this->assertEquals(200, $result->code);
        $this->assertEquals('Renew option set successfully', $result->message);
    }
}
