<?php

use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\Contact;
use Level23\Dynadot\Dto\DomainRegistrationRequest;

require '../vendor/autoload.php';

$apiKey    = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret, true);

    echo "Making domain registration request...\n";

    // Create a registrant contact
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

    // Create domain registration request
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

    // Register the domain
    $result = $client->registerDomain('example.com', $registrationData);

    echo "Domain Registration Results:\n";
    echo "============================\n";
    print_r($result);

    // Example: You can also use existing contacts by ID
    // First, get a list of existing contacts
    $contactList = $client->getContactList();
    if (! empty($contactList->contacts)) {
        $existingContact = $contactList->contacts[0];
        echo "Using existing contact: " . $existingContact->name . " (ID: " . $existingContact->contactId . ")\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
