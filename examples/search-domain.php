<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    // Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret);
    
    echo "Making domain search request...\n";
    
    // Search for a specific domain
    $domainToSearch = 'sdfjsdkfhskjfhs.com';
    $searchResult = $client->search($domainToSearch, true, 'usd');
 
    echo "Search Results for: {$domainToSearch}\n";
    echo "==============================\n";
    echo "Domain: " . $searchResult->domainName . "\n";
    echo "Available: " . $searchResult->available . "\n";
    echo "Premium: " . $searchResult->premium . "\n";
    
    if (!empty($searchResult->priceList)) {
        echo "\nPricing Information:\n";
        echo "-------------------\n";
        foreach ($searchResult->priceList as $price) {
            echo "Currency: " . $price->currency . "\n";
            echo "Unit: " . $price->unit . "\n";
            echo "Transfer: " . $price->transfer . "\n";
            echo "Restore: " . $price->restore . "\n";
            echo "---\n";
        }
    } else {
        echo "\nNo pricing information available.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 