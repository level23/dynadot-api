<?php

use Level23\Dynadot\Client;

require '../vendor/autoload.php';

$apiKey = file_get_contents('.key');
$apiSecret = file_get_contents('.secret');

try {
    $api = new Client($apiKey, $apiSecret);
    $contactList = $api->getContactList();
    
    echo "Found " . count($contactList->contacts) . " contacts:\n\n";
    
    foreach ($contactList->contacts as $index => $contact) {
        echo "Contact " . ($index + 1) . ":\n";
        echo "  ID: " . $contact->contactId . "\n";
        echo "  Name: " . $contact->name . "\n";
        echo "  Organization: " . $contact->organization . "\n";
        echo "  Email: " . $contact->email . "\n";
        echo "  Phone: " . $contact->phoneCc . " " . $contact->phoneNumber . "\n";
        echo "  Address: " . $contact->address1 . "\n";
        if (!empty($contact->address2)) {
            echo "         " . $contact->address2 . "\n";
        }
        echo "  City: " . $contact->city . ", " . $contact->state . " " . $contact->zip . "\n";
        echo "  Country: " . $contact->country . "\n";
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 