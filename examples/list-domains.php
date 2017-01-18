<?php

use Level23\Dynadot\DynadotApi;

require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');

try {
    $api = new DynadotApi($apiKey);
    $domain = $api->listDomains();
} catch (Exception $e) {
    // ...
}
