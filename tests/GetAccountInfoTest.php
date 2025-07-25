<?php

namespace Level23\Dynadot\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\AccountInfoResult;
use PHPUnit\Framework\TestCase;

class GetAccountInfoTest extends TestCase
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

    public function testGetAccountInfoHydration(): void
    {
        $mockResponse = [
            'code'    => 200,
            'message' => 'OK',
            'data'    => [
                'account_info' => [
                    'username'        => 'testuser',
                    'forum_name'      => 'testforum',
                    'avatar_url'      => 'https://example.com/avatar.png',
                    'account_contact' => [
                        'organization' => 'TestOrg',
                        'name'         => 'Test User',
                        'email'        => 'test@example.com',
                        'phone_number' => '1234567890',
                        'phone_cc'     => '1',
                        'fax_number'   => '',
                        'fax_cc'       => '',
                        'address1'     => '123 Main St',
                        'address2'     => '',
                        'city'         => 'Testville',
                        'state'        => 'TS',
                        'zip'          => '12345',
                        'country'      => 'US',
                    ],
                    'customer_since'                => 1234567890,
                    'account_lock'                  => 'enabled',
                    'custom_time_zone'              => 'UTC',
                    'default_registrant_contact_id' => 1,
                    'default_admin_contact_id'      => 2,
                    'default_technical_contact_id'  => 3,
                    'default_billing_contact_id'    => 4,
                    'default_name_server_settings'  => [
                        'type'             => 'custom',
                        'with_ads'         => 'no',
                        'forward_to'       => '',
                        'forward_type'     => '',
                        'website_title'    => '',
                        'ttl'              => '3600',
                        'email_forwarding' => [
                            'type' => 'catchall',
                        ],
                    ],
                    'total_spending'  => '100.00',
                    'price_level'     => '1',
                    'account_balance' => '50.00',
                    'balance_list'    => [
                        [
                            'currency' => 'USD',
                            'amount'   => '50.00',
                        ],
                    ],
                ],
            ],
        ];

        $this->mockHandler->append(
            new Response(200, [], json_encode($mockResponse) ?: '')
        );

        $result = $this->client->getAccountInfo();
        $this->assertInstanceOf(AccountInfoResult::class, $result);
        $this->assertEquals('testuser', $result->accountInfo->username);
        $this->assertEquals('testforum', $result->accountInfo->forumName);
        $this->assertEquals('https://example.com/avatar.png', $result->accountInfo->avatarUrl);
        $this->assertEquals('TestOrg', $result->accountInfo->accountContact->organization);
        $this->assertEquals('Test User', $result->accountInfo->accountContact->name);
        $this->assertEquals('test@example.com', $result->accountInfo->accountContact->email);
        $this->assertEquals('1234567890', $result->accountInfo->accountContact->phoneNumber);
        $this->assertEquals('1', $result->accountInfo->accountContact->phoneCc);
        $this->assertEquals('123 Main St', $result->accountInfo->accountContact->address1);
        $this->assertEquals('Testville', $result->accountInfo->accountContact->city);
        $this->assertEquals('TS', $result->accountInfo->accountContact->state);
        $this->assertEquals('12345', $result->accountInfo->accountContact->zip);
        $this->assertEquals('US', $result->accountInfo->accountContact->country);
        $this->assertEquals(1234567890, $result->accountInfo->customerSince);
        $this->assertEquals('enabled', $result->accountInfo->accountLock);
        $this->assertEquals('UTC', $result->accountInfo->customTimeZone);
        $this->assertEquals(1, $result->accountInfo->defaultRegistrantContactId);
        $this->assertEquals(2, $result->accountInfo->defaultAdminContactId);
        $this->assertEquals(3, $result->accountInfo->defaultTechnicalContactId);
        $this->assertEquals(4, $result->accountInfo->defaultBillingContactId);
        $this->assertEquals('custom', $result->accountInfo->defaultNameServerSettings->type);
        $this->assertEquals('no', $result->accountInfo->defaultNameServerSettings->withAds);
        $this->assertEquals('3600', $result->accountInfo->defaultNameServerSettings->ttl);
        $this->assertEquals('catchall', $result->accountInfo->defaultNameServerSettings->emailForwarding->type);
        $this->assertEquals('100.00', $result->accountInfo->totalSpending);
        $this->assertEquals('1', $result->accountInfo->priceLevel);
        $this->assertEquals('50.00', $result->accountInfo->accountBalance);
        $this->assertCount(1, $result->accountInfo->balanceList);
        $this->assertEquals('USD', $result->accountInfo->balanceList[0]->currency);
        $this->assertEquals('50.00', $result->accountInfo->balanceList[0]->amount);
    }
}
