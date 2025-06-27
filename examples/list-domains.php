<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
// Create the Dynadot API client
    $client = new Client($apiKey, $apiSecret);
    
    echo "Making API request...\n";
     
    // Get the list of all domains
    $domainList = $client->getDomainList();

    foreach ($domainList->domains as $domain) {
        echo $domain->domainName . "\n";
        echo $domain->expiration . "\n";
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
