<?php

namespace Level23\Dynadot\Tests;

use Level23\Dynadot\Dto\BulkSearchDomainResult;
use Level23\Dynadot\Dto\BulkSearchResult;
use Level23\Dynadot\Dto\Contact;
use Level23\Dynadot\Dto\ContactListResult;
use Level23\Dynadot\Dto\DomainInfo;
use Level23\Dynadot\Dto\DomainListResult;
use Level23\Dynadot\Dto\DomainRegistrationRequest;
use Level23\Dynadot\Dto\DomainRegistrationResult;
use Level23\Dynadot\Dto\NameserverUpdateResult;
use Level23\Dynadot\Dto\PriceList;
use Level23\Dynadot\Dto\RenewOptionResult;
use Level23\Dynadot\Dto\SearchResult;
use PHPUnit\Framework\TestCase;

class DtoSerializationTest extends TestCase
{
    public function testContactJsonSerialize(): void
    {
        $contact = Contact::create(
            'Test Organization',
            'John Doe',
            'john.doe@example.com',
            '1234567890',
            '1',
            '123 Main St',
            'New York',
            'NY',
            '10001',
            'US'
        );

        $serialized = $contact->jsonSerialize();

        $this->assertEquals('Test Organization', $serialized['organization']);
        $this->assertEquals('John Doe', $serialized['name']);
        $this->assertEquals('john.doe@example.com', $serialized['email']);
        $this->assertEquals('1234567890', $serialized['phone_number']);
        $this->assertEquals('1', $serialized['phone_cc']);
        $this->assertEquals('123 Main St', $serialized['address1']);
        $this->assertEquals('New York', $serialized['city']);
        $this->assertEquals('NY', $serialized['state']);
        $this->assertEquals('10001', $serialized['zip']);
        $this->assertEquals('US', $serialized['country']);
    }

    public function testContactJsonSerializeWithOptionalFields(): void
    {
        $contact = Contact::create(
            'Test Organization',
            'John Doe',
            'john.doe@example.com',
            '1234567890',
            '1',
            '123 Main St',
            'New York',
            'NY',
            '10001',
            'US',
            'Suite 100', // address2
            '5559876543', // fax
            '1' // fax cc
        );

        $serialized = $contact->jsonSerialize();

        $this->assertEquals('Suite 100', $serialized['address2']);
        $this->assertEquals('5559876543', $serialized['fax_number']);
    }

    public function testDomainRegistrationRequestJsonSerialize(): void
    {
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

        $registrationData = DomainRegistrationRequest::create(
            2,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com'],
            'on',
            'USD',
            true,
            'COUPON10',
            11111, // registrant contact ID
            22222, // admin contact ID
            33333, // tech contact ID
            44444, // billing contact ID
            $registrantContact,
            null, // admin contact
            null, // tech contact
            null  // billing contact
        );

        $serialized = $registrationData->jsonSerialize();

        $this->assertArrayHasKey('domain', $serialized);
        $this->assertArrayHasKey('currency', $serialized);
        $this->assertArrayHasKey('register_premium', $serialized);
        $this->assertArrayHasKey('coupon_code', $serialized);

        $domain = $serialized['domain'];
        $this->assertEquals(2, $domain['duration']);
        $this->assertEquals('AUTH123', $domain['auth_code']);
        $this->assertEquals(12345, $domain['customer_id']);
        $this->assertEquals(11111, $domain['registrant_contact_id']);
        $this->assertEquals(22222, $domain['admin_contact_id']);
        $this->assertEquals(33333, $domain['tech_contact_id']);
        $this->assertEquals(44444, $domain['billing_contact_id']);
        $this->assertEquals(['ns1.example.com', 'ns2.example.com'], $domain['name_server_list']);
        $this->assertEquals('on', $domain['privacy']);

        // Check that nested contact is serialized
        $this->assertIsArray($domain['registrant_contact']);
        $this->assertEquals('Registrant Corp', $domain['registrant_contact']['organization']);

        $this->assertEquals('USD', $serialized['currency']);
        $this->assertTrue($serialized['register_premium']);
        $this->assertEquals('COUPON10', $serialized['coupon_code']);
    }

    public function testDomainRegistrationRequestJsonSerializeWithNullValues(): void
    {
        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com']
        );

        $serialized = $registrationData->jsonSerialize();

        $domain = $serialized['domain'];

        // Null values should be filtered out
        $this->assertArrayNotHasKey('registrant_contact_id', $domain);
        $this->assertArrayNotHasKey('admin_contact_id', $domain);
        $this->assertArrayNotHasKey('tech_contact_id', $domain);
        $this->assertArrayNotHasKey('billing_contact_id', $domain);
        $this->assertArrayNotHasKey('registrant_contact', $domain);
        $this->assertArrayNotHasKey('admin_contact', $domain);
        $this->assertArrayNotHasKey('tech_contact', $domain);
        $this->assertArrayNotHasKey('billing_contact', $domain);
        // Note: coupon_code is not filtered out in the current implementation
        $this->assertNull($serialized['coupon_code']);
    }

    public function testDomainRegistrationResultJsonSerialize(): void
    {
        $result = DomainRegistrationResult::fromArray([
            'domain_name'     => 'example.com',
            'expiration_date' => 1735689600,
        ]);

        $serialized = $result->jsonSerialize();

        $this->assertEquals('example.com', $serialized['domain_name']);
        $this->assertEquals(1735689600, $serialized['expiration_date']);
    }

    public function testDomainInfoJsonSerialize(): void
    {
        $domainInfo = DomainInfo::fromArray([
            'domainName'           => 'example.com',
            'expiration'           => 1735689600,
            'registration'         => 1640995200,
            'glueInfo'             => [],
            'registrant_contactId' => 12345,
            'admin_contactId'      => 12346,
            'tech_contactId'       => 12347,
            'billing_contactId'    => 12348,
            'locked'               => false,
            'disabled'             => false,
            'udrpLocked'           => false,
            'hold'                 => false,
            'privacy'              => 'off',
            'is_for_sale'          => false,
            'renew_option'         => 'auto',
            'note'                 => null,
            'folder_id'            => 1,
            'folder_name'          => 'Default',
            'status'               => 'active',
        ]);

        $serialized = $domainInfo->jsonSerialize();

        $this->assertEquals('example.com', $serialized['domainName']);
        $this->assertEquals(1735689600, $serialized['expiration']);
        $this->assertEquals(1640995200, $serialized['registration']);
        $this->assertEquals('active', $serialized['status']);
        $this->assertEquals(12345, $serialized['registrant_contact_id']);
        $this->assertEquals(12346, $serialized['admin_contact_id']);
        $this->assertEquals(12347, $serialized['tech_contact_id']);
        $this->assertEquals(12348, $serialized['billing_contact_id']);
    }

    public function testPriceListJsonSerialize(): void
    {
        $priceList = PriceList::fromArray([
            'currency'     => 'USD',
            'unit'         => 'year',
            'registration' => '12.99',
            'renewal'      => '14.99',
            'transfer'     => '11.99',
            'restore'      => '25.99',
        ]);

        $serialized = $priceList->jsonSerialize();

        $this->assertEquals('USD', $serialized['currency']);
        $this->assertEquals('year', $serialized['unit']);
        $this->assertEquals('12.99', $serialized['registration']);
        $this->assertEquals('14.99', $serialized['renewal']);
        $this->assertEquals('11.99', $serialized['transfer']);
        $this->assertEquals('25.99', $serialized['restore']);
    }

    public function testSearchResultJsonSerialize(): void
    {
        $searchResult = SearchResult::fromArray([
            'domain_name' => 'example.com',
            'available'   => '1',
            'premium'     => '0',
            'price_list'  => [
                [
                    'currency'     => 'USD',
                    'unit'         => 'year',
                    'registration' => '12.99',
                    'renewal'      => '14.99',
                    'transfer'     => '11.99',
                    'restore'      => '25.99',
                ],
            ],
        ]);

        $serialized = $searchResult->jsonSerialize();

        $this->assertEquals('example.com', $serialized['domain_name']);
        $this->assertEquals('1', $serialized['available']);
        $this->assertEquals('0', $serialized['premium']);
        $this->assertIsArray($serialized['price_list']);
        $this->assertCount(1, $serialized['price_list']);
        $this->assertEquals('USD', $serialized['price_list'][0]['currency']);
        $this->assertEquals('12.99', $serialized['price_list'][0]['registration']);
    }

    public function testBulkSearchDomainResultJsonSerialize(): void
    {
        $bulkSearchDomainResult = BulkSearchDomainResult::fromArray([
            'domain_name' => 'example.com',
            'available'   => '1',
        ]);

        $serialized = $bulkSearchDomainResult->jsonSerialize();

        $this->assertEquals('example.com', $serialized['domain_name']);
        $this->assertEquals('1', $serialized['available']);
    }

    public function testBulkSearchResultJsonSerialize(): void
    {
        $bulkSearchResult = BulkSearchResult::fromArray([
            'domain_result_list' => [
                ['domain_name' => 'example.com', 'available' => '1'],
                ['domain_name' => 'test.com', 'available' => '0'],
            ],
        ]);

        $serialized = $bulkSearchResult->jsonSerialize();

        $this->assertArrayHasKey('domain_result_list', $serialized);
        $this->assertIsArray($serialized['domain_result_list']);
        $this->assertCount(2, $serialized['domain_result_list']);

        $this->assertEquals('example.com', $serialized['domain_result_list'][0]['domain_name']);
        $this->assertEquals('1', $serialized['domain_result_list'][0]['available']);
        $this->assertEquals('test.com', $serialized['domain_result_list'][1]['domain_name']);
        $this->assertEquals('0', $serialized['domain_result_list'][1]['available']);
    }

    public function testDomainListResultJsonSerialize(): void
    {
        $domainListResult = DomainListResult::fromArray([
            'domainInfo' => [
                [
                    'domainName'           => 'example.com',
                    'expiration'           => 1735689600,
                    'registration'         => 1640995200,
                    'glueInfo'             => [],
                    'registrant_contactId' => 12345,
                    'admin_contactId'      => 12346,
                    'tech_contactId'       => 12347,
                    'billing_contactId'    => 12348,
                    'locked'               => false,
                    'disabled'             => false,
                    'udrpLocked'           => false,
                    'hold'                 => false,
                    'privacy'              => 'off',
                    'is_for_sale'          => false,
                    'renew_option'         => 'auto',
                    'note'                 => null,
                    'folder_id'            => 1,
                    'folder_name'          => 'Default',
                    'status'               => 'active',
                ],
                [
                    'domainName'           => 'test.com',
                    'expiration'           => 1735776000,
                    'registration'         => 1641081600,
                    'glueInfo'             => [],
                    'registrant_contactId' => 12349,
                    'admin_contactId'      => 12350,
                    'tech_contactId'       => 12351,
                    'billing_contactId'    => 12352,
                    'locked'               => false,
                    'disabled'             => false,
                    'udrpLocked'           => false,
                    'hold'                 => false,
                    'privacy'              => 'off',
                    'is_for_sale'          => false,
                    'renew_option'         => 'auto',
                    'note'                 => null,
                    'folder_id'            => 1,
                    'folder_name'          => 'Default',
                    'status'               => 'active',
                ],
            ],
        ]);

        $serialized = $domainListResult->jsonSerialize();

        $this->assertArrayHasKey('domains', $serialized);
        $this->assertIsArray($serialized['domains']);
        $this->assertCount(2, $serialized['domains']);

        $this->assertEquals('example.com', $serialized['domains'][0]['domainName']);
        $this->assertEquals(1735689600, $serialized['domains'][0]['expiration']);
        $this->assertEquals('test.com', $serialized['domains'][1]['domainName']);
        $this->assertEquals(1735776000, $serialized['domains'][1]['expiration']);
    }

    public function testContactListResultJsonSerialize(): void
    {
        $contactListResult = ContactListResult::fromArray([
            'contact_list' => [
                [
                    'contact_id'   => 12345,
                    'organization' => 'Test Corp',
                    'name'         => 'John Doe',
                    'email'        => 'john@test.com',
                    'phone_number' => '1234567890',
                    'phone_cc'     => '1',
                    'address1'     => '123 Main St',
                    'city'         => 'New York',
                    'state'        => 'NY',
                    'zip'          => '10001',
                    'country'      => 'US',
                ],
                [
                    'contact_id'   => 12346,
                    'organization' => 'Another Corp',
                    'name'         => 'Jane Smith',
                    'email'        => 'jane@another.com',
                    'phone_number' => '0987654321',
                    'phone_cc'     => '1',
                    'address1'     => '456 Oak St',
                    'city'         => 'Los Angeles',
                    'state'        => 'CA',
                    'zip'          => '90210',
                    'country'      => 'US',
                ],
            ],
        ]);

        $serialized = $contactListResult->jsonSerialize();

        $this->assertArrayHasKey('contact_list', $serialized);
        $this->assertIsArray($serialized['contact_list']);
        $this->assertCount(2, $serialized['contact_list']);

        $this->assertEquals(12345, $serialized['contact_list'][0]['contact_id']);
        $this->assertEquals('Test Corp', $serialized['contact_list'][0]['organization']);
        $this->assertEquals('John Doe', $serialized['contact_list'][0]['name']);
        $this->assertEquals(12346, $serialized['contact_list'][1]['contact_id']);
        $this->assertEquals('Another Corp', $serialized['contact_list'][1]['organization']);
        $this->assertEquals('Jane Smith', $serialized['contact_list'][1]['name']);
    }

    public function testRenewOptionResultJsonSerialize(): void
    {
        $renewOptionResult = RenewOptionResult::fromArray([
            'code'    => 200,
            'message' => 'Renew option updated successfully',
        ]);

        $serialized = $renewOptionResult->jsonSerialize();

        $this->assertEquals(200, $serialized['code']);
        $this->assertEquals('Renew option updated successfully', $serialized['message']);
    }

    public function testNameserverUpdateResultJsonSerialize(): void
    {
        $nameserverUpdateResult = NameserverUpdateResult::fromArray([
            'code'    => 200,
            'message' => 'Nameservers updated successfully',
        ]);

        $serialized = $nameserverUpdateResult->jsonSerialize();

        $this->assertEquals(200, $serialized['code']);
        $this->assertEquals('Nameservers updated successfully', $serialized['message']);
    }

    public function testJsonEncodeIntegration(): void
    {
        // Test that json_encode works with DTOs
        $contact = Contact::create(
            'Test Organization',
            'John Doe',
            'john.doe@example.com',
            '1234567890',
            '1',
            '123 Main St',
            'New York',
            'NY',
            '10001',
            'US'
        );

        $json = json_encode($contact);
        $this->assertNotFalse($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('Test Organization', $decoded['organization']);
        $this->assertEquals('John Doe', $decoded['name']);
    }

    public function testNestedJsonEncodeIntegration(): void
    {
        // Test that nested DTOs serialize correctly
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

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com'],
            'on',
            'USD',
            false,
            null,
            null,
            null,
            null,
            null,
            $registrantContact
        );

        $json = json_encode($registrationData);
        $this->assertNotFalse($json);

        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('domain', $decoded);
        $this->assertArrayHasKey('registrant_contact', $decoded['domain']);
        $this->assertEquals('Registrant Corp', $decoded['domain']['registrant_contact']['organization']);
    }

    public function testClientIntegrationWithJsonSerialize(): void
    {
        // Test that the client actually uses jsonSerialize when making requests
        $registrantContact = Contact::create(
            'Test Corp',
            'John Doe',
            'john@test.com',
            '1234567890',
            '1',
            '123 Main St',
            'New York',
            'NY',
            '10001',
            'US'
        );

        $registrationData = DomainRegistrationRequest::create(
            1,
            'AUTH123',
            12345,
            ['ns1.example.com', 'ns2.example.com'],
            'on',
            'USD',
            false,
            null,
            null,
            null,
            null,
            null,
            $registrantContact
        );

        // This simulates what the client does internally
        $serializedData = $registrationData->jsonSerialize();

        $this->assertArrayHasKey('domain', $serializedData);
        $this->assertArrayHasKey('currency', $serializedData);

        $domain = $serializedData['domain'];
        $this->assertEquals(1, $domain['duration']);
        $this->assertEquals('AUTH123', $domain['auth_code']);
        $this->assertEquals(12345, $domain['customer_id']);
        $this->assertEquals(['ns1.example.com', 'ns2.example.com'], $domain['name_server_list']);
        $this->assertEquals('on', $domain['privacy']);

        // Verify nested contact is properly serialized
        $this->assertArrayHasKey('registrant_contact', $domain);
        $this->assertIsArray($domain['registrant_contact']);
        $this->assertEquals('Test Corp', $domain['registrant_contact']['organization']);
        $this->assertEquals('John Doe', $domain['registrant_contact']['name']);
        $this->assertEquals('john@test.com', $domain['registrant_contact']['email']);

        $this->assertEquals('USD', $serializedData['currency']);
        $this->assertFalse($serializedData['register_premium']);
    }
}
