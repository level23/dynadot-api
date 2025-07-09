<?php

namespace Level23\Dynadot\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\PriceList;
use Level23\Dynadot\Dto\SearchResult;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

class SearchTest extends TestCase
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

    public function testSearchAvailableDomain(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'example.com',
                'available'   => 'yes',
                'premium'     => 'no',
                'price_list'  => [
                    [
                        'currency'     => 'USD',
                        'unit'         => 'year',
                        'registration' => '12.99',
                        'renewal'      => '14.99',
                        'transfer'     => '11.99',
                        'restore'      => '89.99',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('example.com');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('example.com', $result->domainName);
        $this->assertEquals('yes', $result->available);
        $this->assertEquals('no', $result->premium);
        $this->assertCount(1, $result->priceList);

        $price = $result->priceList[0];
        $this->assertInstanceOf(PriceList::class, $price);
        $this->assertEquals('USD', $price->currency);
        $this->assertEquals('year', $price->unit);
        $this->assertEquals('12.99', $price->registration);
        $this->assertEquals('14.99', $price->renewal);
        $this->assertEquals('11.99', $price->transfer);
        $this->assertEquals('89.99', $price->restore);
    }

    public function testSearchUnavailableDomain(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'google.com',
                'available'   => 'no',
                'premium'     => 'no',
                'price_list'  => [],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('google.com');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('google.com', $result->domainName);
        $this->assertEquals('no', $result->available);
        $this->assertEquals('no', $result->premium);
        $this->assertCount(0, $result->priceList);
    }

    public function testSearchPremiumDomain(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'premium.com',
                'available'   => 'yes',
                'premium'     => 'yes',
                'price_list'  => [
                    [
                        'currency'     => 'USD',
                        'unit'         => 'year',
                        'registration' => '999.99',
                        'renewal'      => '999.99',
                        'transfer'     => '999.99',
                        'restore'      => '999.99',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('premium.com');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('premium.com', $result->domainName);
        $this->assertEquals('yes', $result->available);
        $this->assertEquals('yes', $result->premium);
        $this->assertCount(1, $result->priceList);

        $price = $result->priceList[0];
        $this->assertEquals('999.99', $price->registration);
    }

    public function testSearchWithPriceAndCurrency(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'example.com',
                'available'   => 'yes',
                'premium'     => 'no',
                'price_list'  => [
                    [
                        'currency'     => 'EUR',
                        'unit'         => 'year',
                        'registration' => '15.99',
                        'renewal'      => '17.99',
                        'transfer'     => '14.99',
                        'restore'      => '99.99',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('example.com', true, 'EUR');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('example.com', $result->domainName);
        $this->assertEquals('yes', $result->available);
        $this->assertEquals('no', $result->premium);
        $this->assertCount(1, $result->priceList);

        $price = $result->priceList[0];
        $this->assertEquals('EUR', $price->currency);
        $this->assertEquals('15.99', $price->registration);
    }

    public function testSearchWithoutPrice(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'example.com',
                'available'   => 'yes',
                'premium'     => 'no',
                'price_list'  => [],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('example.com', false);

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('example.com', $result->domainName);
        $this->assertEquals('yes', $result->available);
        $this->assertEquals('no', $result->premium);
        $this->assertCount(0, $result->priceList);
    }

    public function testSearchMultipleCurrencies(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'example.com',
                'available'   => 'yes',
                'premium'     => 'no',
                'price_list'  => [
                    [
                        'currency'     => 'USD',
                        'unit'         => 'year',
                        'registration' => '12.99',
                        'renewal'      => '14.99',
                        'transfer'     => '11.99',
                        'restore'      => '89.99',
                    ],
                    [
                        'currency'     => 'EUR',
                        'unit'         => 'year',
                        'registration' => '15.99',
                        'renewal'      => '17.99',
                        'transfer'     => '14.99',
                        'restore'      => '99.99',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('example.com', true, 'USD');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('example.com', $result->domainName);
        $this->assertCount(2, $result->priceList);

        $usdPrice = $result->priceList[0];
        $this->assertEquals('USD', $usdPrice->currency);
        $this->assertEquals('12.99', $usdPrice->registration);

        $eurPrice = $result->priceList[1];
        $this->assertEquals('EUR', $eurPrice->currency);
        $this->assertEquals('15.99', $eurPrice->registration);
    }

    public function testSearchDomainNotFound(): void
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

        $this->client->search('invalid-domain');
    }

    public function testSearchApiError(): void
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

        $this->client->search('invalid');
    }

    public function testSearchNetworkError(): void
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

        $this->client->search('example.com');
    }

    public function testSearchInvalidJsonResponse(): void
    {
        $this->mockHandler->append(
            new Response(400, [], 'invalid json')
        );

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Invalid JSON in error response: Syntax error');

        $this->client->search('example.com');
    }

    public function testSearchMissingDataKey(): void
    {
        $mockResponse = [
            'code' => 200,
            // Missing 'data' key
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('example.com');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('', $result->domainName);
        $this->assertEquals('', $result->available);
        $this->assertEquals('', $result->premium);
        $this->assertCount(0, $result->priceList);
    }

    public function testSearchWithSpecialCharacters(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'example-domain.com',
                'available'   => 'yes',
                'premium'     => 'no',
                'price_list'  => [
                    [
                        'currency'     => 'USD',
                        'unit'         => 'year',
                        'registration' => '12.99',
                        'renewal'      => '14.99',
                        'transfer'     => '11.99',
                        'restore'      => '89.99',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('example-domain.com');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('example-domain.com', $result->domainName);
        $this->assertEquals('yes', $result->available);
        $this->assertEquals('no', $result->premium);
    }

    public function testSearchWithSubdomain(): void
    {
        $mockResponse = [
            'code' => 200,
            'data' => [
                'domain_name' => 'sub.example.com',
                'available'   => 'yes',
                'premium'     => 'no',
                'price_list'  => [
                    [
                        'currency'     => 'USD',
                        'unit'         => 'year',
                        'registration' => '12.99',
                        'renewal'      => '14.99',
                        'transfer'     => '11.99',
                        'restore'      => '89.99',
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->search('sub.example.com');

        $this->assertInstanceOf(SearchResult::class, $result);
        $this->assertEquals('sub.example.com', $result->domainName);
        $this->assertEquals('yes', $result->available);
        $this->assertEquals('no', $result->premium);
    }
}
