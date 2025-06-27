<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret);
    
    echo "Making bulk domain search request...\n";
    
    // Define domains to search for
    $domains = [
        'example.com',
        'test.org',
        'mydomain.net',
        'another-domain.co.uk'
    ];
    
    // Perform bulk search
    $searchResults = $client->bulkSearch($domains);

    echo "Search Results:\n";
    echo "===============\n";
    
    foreach ($searchResults->domainResults as $result) {
        echo "Domain: " . $result->domainName . "\n";
        echo "Available: " . $result->available . "\n";
        echo "---\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 