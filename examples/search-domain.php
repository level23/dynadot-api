<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Level23\Dynadot\Client;

$apiKey    = getenv('DYNADOT_API_KEY');
$apiSecret = getenv('DYNADOT_API_SECRET');

$client = new Client($apiKey, $apiSecret);

try {
    $domainToSearch = 'sdfjsdkfhskjfhs.com';
    $searchResult   = $client->search($domainToSearch, true, 'usd');
    echo json_encode($searchResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
