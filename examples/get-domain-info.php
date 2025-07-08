<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey    = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret);

    echo "Making domain info request...\n";

    // Get domain information
    $domainName       = 'freshcontffoffer.xyz';
    $domainInfoResult = $client->getDomainInfo($domainName);

    echo "Domain Information Results:\n";
    echo "==========================\n";
    print_r($domainInfoResult);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
