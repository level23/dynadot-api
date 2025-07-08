<?php

namespace Level23\Dynadot\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\BulkSearchDomainResult;
use Level23\Dynadot\Dto\BulkSearchResult;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

class BulkSearchTest extends TestCase
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

    public function testBulkSearchSuccess(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_result_list' => [
                    [
                        'domain_name' => 'example.com',
                        'available'   => 'yes',
                    ],
                    [
                        'domain_name' => 'test.com',
                        'available'   => 'no',
                    ],
                    [
                        'domain_name' => 'available.org',
                        'available'   => 'yes',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $domains = ['example.com', 'test.com', 'available.org'];
        $result  = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(3, $result->domainResults);

        // Test first domain
        $firstDomain = $result->domainResults[0];
        $this->assertInstanceOf(BulkSearchDomainResult::class, $firstDomain);
        $this->assertEquals('example.com', $firstDomain->domainName);
        $this->assertEquals('yes', $firstDomain->available);

        // Test second domain
        $secondDomain = $result->domainResults[1];
        $this->assertInstanceOf(BulkSearchDomainResult::class, $secondDomain);
        $this->assertEquals('test.com', $secondDomain->domainName);
        $this->assertEquals('no', $secondDomain->available);

        // Test third domain
        $thirdDomain = $result->domainResults[2];
        $this->assertInstanceOf(BulkSearchDomainResult::class, $thirdDomain);
        $this->assertEquals('available.org', $thirdDomain->domainName);
        $this->assertEquals('yes', $thirdDomain->available);
    }

    public function testBulkSearchSingleDomain(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_result_list' => [
                    [
                        'domain_name' => 'single.com',
                        'available'   => 'yes',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $domains = ['single.com'];
        $result  = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(1, $result->domainResults);

        $domain = $result->domainResults[0];
        $this->assertInstanceOf(BulkSearchDomainResult::class, $domain);
        $this->assertEquals('single.com', $domain->domainName);
        $this->assertEquals('yes', $domain->available);
    }

    public function testBulkSearchEmptyArray(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_result_list' => [],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $domains = [];
        $result  = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(0, $result->domainResults);
    }

    public function testBulkSearchWithWhitespace(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_result_list' => [
                    [
                        'domain_name' => 'example.com',
                        'available'   => 'yes',
                    ],
                    [
                        'domain_name' => 'test.com',
                        'available'   => 'no',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $domains = [' example.com ', ' test.com '];
        $result  = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(2, $result->domainResults);

        $firstDomain = $result->domainResults[0];
        $this->assertEquals('example.com', $firstDomain->domainName);
        $this->assertEquals('yes', $firstDomain->available);

        $secondDomain = $result->domainResults[1];
        $this->assertEquals('test.com', $secondDomain->domainName);
        $this->assertEquals('no', $secondDomain->available);
    }

    public function testBulkSearchApiError(): void
    {
        $mockResponse = [
            'code'    => 400,
            'message' => 'Invalid domain format',
        ];

        $this->mockHandler->append(
            new Response(400, [], json_encode($mockResponse) ?: '')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid domain format');

        $domains = ['invalid-domain'];
        $this->client->bulkSearch($domains);
    }

    public function testBulkSearchNetworkError(): void
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

        $domains = ['example.com', 'test.com'];
        $this->client->bulkSearch($domains);
    }

    public function testBulkSearchInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $domains = ['example.com'];
        $this->client->bulkSearch($domains);
    }

    public function testBulkSearchMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200,
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $domains = ['example.com'];
        $result  = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(0, $result->domainResults);
    }

    public function testBulkSearchMissingDomainResultListKey(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                // Missing 'domain_result_list' key
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $domains = ['example.com'];
        $result  = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(0, $result->domainResults);
    }

    public function testBulkSearchWithSpecialCharacters(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_result_list' => [
                    [
                        'domain_name' => 'example-domain.com',
                        'available'   => 'yes',
                    ],
                    [
                        'domain_name' => 'test_subdomain.com',
                        'available'   => 'no',
                    ],
                    [
                        'domain_name' => 'example.co.uk',
                        'available'   => 'yes',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $domains = ['example-domain.com', 'test_subdomain.com', 'example.co.uk'];
        $result  = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(3, $result->domainResults);

        $firstDomain = $result->domainResults[0];
        $this->assertEquals('example-domain.com', $firstDomain->domainName);
        $this->assertEquals('yes', $firstDomain->available);

        $secondDomain = $result->domainResults[1];
        $this->assertEquals('test_subdomain.com', $secondDomain->domainName);
        $this->assertEquals('no', $secondDomain->available);

        $thirdDomain = $result->domainResults[2];
        $this->assertEquals('example.co.uk', $thirdDomain->domainName);
        $this->assertEquals('yes', $thirdDomain->available);
    }

    public function testBulkSearchLargeList(): void
    {
        $domainResults = [];
        $domains       = [];

        for ($i = 1; $i <= 10; $i++) {
            $domainName      = "domain{$i}.com";
            $domains[]       = $domainName;
            $domainResults[] = [
                'domain_name' => $domainName,
                'available'   => ($i % 2 == 0) ? 'yes' : 'no',
            ];
        }

        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_result_list' => $domainResults,
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->bulkSearch($domains);

        $this->assertInstanceOf(BulkSearchResult::class, $result);
        $this->assertCount(10, $result->domainResults);

        foreach ($result->domainResults as $index => $domainResult) {
            $this->assertInstanceOf(BulkSearchDomainResult::class, $domainResult);
            $this->assertEquals("domain" . ($index + 1) . ".com", $domainResult->domainName);
            $this->assertEquals(($index + 1) % 2 == 0 ? 'yes' : 'no', $domainResult->available);
        }
    }
}
