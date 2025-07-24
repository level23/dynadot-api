<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Level23\Dynadot\Client;

$apiKey    = getenv('DYNADOT_API_KEY');
$apiSecret = getenv('DYNADOT_API_SECRET');

$client = new Client($apiKey, $apiSecret);

try {
    $contactId   = 1479250;
    $contactInfo = $client->getContactInfo($contactId);
    echo json_encode($contactInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
