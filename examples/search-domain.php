<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey    = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret, true);

    echo "Making domain search request...\n";

    // Search for a specific domain
    $domainToSearch = 'sdfjsdkfhskjfhs.com';
    $searchResult   = $client->search($domainToSearch, true, 'usd');

    echo "Search Results:\n";
    echo "===============\n";
    print_r($searchResult);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
