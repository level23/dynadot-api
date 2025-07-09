<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey    = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret, true);

    echo "Making domain list request...\n";

    // Get the list of all domains
    $domainList = $client->getDomainList();

    echo "Domain List Results:\n";
    echo "===================\n";
    print_r($domainList);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
