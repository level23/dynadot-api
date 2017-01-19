<?php

use Level23\Dynadot\DynadotApi;
use Monolog\Logger;


require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');

// Create the logger
$logger = new Logger('my_logger');

$logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());
$logger->addInfo('Key: ' . $apiKey);

try {
    $api = new DynadotApi($apiKey, $logger);
    print_r($api->getContactInfo(1234)); //
} catch (Exception $e) {
    echo $e->getMessage();
}
