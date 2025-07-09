<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey    = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret, true);

    echo "Making contact list request...\n";

    // Get the list of all contacts
    $contactList = $client->getContactList();

    echo "Contact List Results:\n";
    echo "====================\n";
    print_r($contactList);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
