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
    $list = $api->getDomainList();

    print_r( $list );
} catch (Exception $e) {
    echo $e->getMessage();
    echo "<br>";
    echo $e->getTraceAsString();
}
