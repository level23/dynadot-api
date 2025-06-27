<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    $api = new Client($apiKey, $apiSecret);
    print_r($api->getContactInfo(1479250));
} catch (Exception $e) {
    echo $e->getMessage();
}
