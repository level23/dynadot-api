<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey    = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret);

    echo "Making contact info request...\n";

    // Get contact information by ID
    $contactId   = 1479250;
    $contactInfo = $client->getContactInfo($contactId);

    echo "Contact Information Results:\n";
    echo "===========================\n";
    print_r($contactInfo);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
