<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\Contact;
use Level23\Dynadot\Dto\DomainRegistrationRequest;

$apiKey    = getenv('DYNADOT_API_KEY');
$apiSecret = getenv('DYNADOT_API_SECRET');

$client = new Client($apiKey, $apiSecret);

try {
    $registrantContact = Contact::create(
        organization: 'Example Corp',
        name: 'John Doe',
        email: 'john.doe@example.com',
        phoneNumber: '1234567890',
        phoneCc: '1',
        address1: '123 Main St',
        city: 'New York',
        state: 'NY',
        zip: '10001',
        country: 'US',
    );

    $registrationData = DomainRegistrationRequest::create(
        duration: 1,
        authCode: '',
        customerId: 0,
        registrant: $registrantContact,
        admin: $registrantContact,
        tech: $registrantContact,
        billing: $registrantContact,
        nameserverList: ['ns1.example.com', 'ns2.example.com'],
        privacy: 'true',
        currency: 'USD',
        registerPremium: false,
        couponCode: '',
    );

    $result = $client->registerDomain('example.com', $registrationData);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
