<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    $client = new Client($apiKey, $apiSecret);
    print_r($client->getDomainInfo('freshcontffoffer.xyz'));
} catch (Exception $e) {
    echo $e->getMessage();
}
