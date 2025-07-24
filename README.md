# Dynadot-API

[![Build](https://github.com/level23/dynadot-api/actions/workflows/build.yml/badge.svg)](https://github.com/level23/dynadot-api/actions/workflows/build.yml)
[![Code Coverage](https://scrutinizer-ci.com/g/level23/dynadot-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/level23/dynadot-api/?branch=master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)
[![Packagist Version](https://img.shields.io/packagist/v/level23/dynadot-api.svg)](https://packagist.org/packages/level23/dynadot-api)
[![Total Downloads](https://img.shields.io/packagist/dt/level23/dynadot-api.svg)](https://packagist.org/packages/level23/dynadot-api)
[![Quality Score](https://scrutinizer-ci.com/g/level23/dynadot-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/level23/dynadot-api/?branch=master)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

Unofficial implementation for the Dynadot domain API

This library provides a PHP client for the Dynadot API, allowing you to manage domains, search for domain availability, and handle domain registrations programmatically.

Before you can use this API you have to:

  * Get the API key and secret from the Dynadot backend
  * Whitelist the IP address where your requests are coming from

By default, we will try to connect to the Dynadot API for 30 seconds. If that fails, 
a `GuzzleHttp\Exception\ConnectException` is thrown. You probably want to catch these in case if something goes wrong.

## Installing

Install the latest version with:

```bash
$ composer require level23/dynadot-api
```

## Requirements

To make use of this API you have to run PHP 8.2 or higher.

## Contributing

If you want to help us improve this implementation, just contact us. All help is welcome!
The only requirement for contributing is that all code is 100% covered by unit tests and that they implement the 
PSR standards.

## License

See the file LICENSE for more information.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and version history.

## Example usage

See below some basic sample usages.

### Getting Domain Details with `getDomainInfo`

```php
<?php
use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $domainInfo = $client->getDomainInfo('example.com');
    
    // process your domain info here
    print_r($domainInfo);
} catch (Exception $e) {
    // ... handle exception
}
```

The returned object will be an instance of `Level23\Dynadot\Dto\DomainListResult` containing domain information.

### List all domains with `getDomainList`

```php
<?php

use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $domainList = $client->getDomainList();

    foreach ($domainList->domains as $domain) {
        echo $domain->domainName . "\n";
        echo $domain->expiration . "\n";
    }
} catch (Exception $e) {
    // ... handle exception
}
```

This will return a `Level23\Dynadot\Dto\DomainListResult` object containing an array of domain objects. An exception will be 
thrown when anything went wrong.

### Set nameservers for a domain with `setNameservers`

```php
<?php

use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $result = $client->setNameservers('example.com', ['ns01.example.com', 'ns2.example.net', 'ns03.example.org']);
    // ...
} catch (Exception $e) {
    // ... handle exception
}
```

The `setNameservers` method returns a `NameserverUpdateResult` object. An exception will be thrown when something 
went wrong.

### Retrieving contact info with `getContactInfo`

```php
<?php
use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $contact = $client->getContactInfo(1234); // 1234 = the contact id
    print_r($contact);
} catch (Exception $e) {
    echo $e->getMessage();
}
```

This returns a `Level23\Dynadot\Dto\Contact` object with the contact details.

### Get list of contacts with `getContactList`

```php
<?php
use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $contactList = $client->getContactList();
    
    foreach ($contactList->contacts as $contact) {
        echo "Contact ID: " . $contact->contactId . "\n";
        echo "Name: " . $contact->name . "\n";
        echo "Email: " . $contact->email . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
```

### Set renew option with `setRenewOption`

```php
<?php

use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $result = $client->setRenewOption('example.com', 'auto');
    // ...
} catch (Exception $e) {
    // ... handle exception
}
```

The `setRenewOption` lets you set the renewal setting for a domain. Values for the second 
argument ($renewOption) can be "donot", "auto", "reset". The method returns a `RenewOptionResult` object.

### Search for domain availability with `search`

```php
<?php

use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $result = $client->search('example.com', true, 'USD');
    
    echo "Domain: " . $result->domainName . "\n";
    echo "Available: " . ($result->available ? 'Yes' : 'No') . "\n";
    if ($result->available && isset($result->price)) {
        echo "Price: $" . $result->price . "\n";
    }
} catch (Exception $e) {
    // ... handle exception
}
```

### Bulk search for multiple domains with `bulkSearch`

```php
<?php

use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $domains = ['example.com', 'test.org', 'mydomain.net'];
    $result = $client->bulkSearch($domains);
    
    foreach ($result->domainResults as $domainResult) {
        echo "Domain: " . $domainResult->domainName . "\n";
        echo "Available: " . ($domainResult->available ? 'Yes' : 'No') . "\n";
        echo "---\n";
    }
} catch (Exception $e) {
    // ... handle exception
}
```

### Register a new domain with `registerDomain`

```php
<?php

use Level23\Dynadot\Client;
use Level23\Dynadot\Dto\Contact;
use Level23\Dynadot\Dto\DomainRegistrationRequest;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    
    // Create contact information
    $registrantContact = Contact::create(
        organization: 'Example Corp',
        name: 'John Doe',
        email: 'john.doe@example.com',
        phoneNumber: '1234567890',
        phoneCc: '1',
        address1: '123 Main St',
        city: 'New York',
        state: 'NY',
        zip: '10001',
        country: 'US',
    );

    // Create domain registration request
    $registrationData = DomainRegistrationRequest::create(
        duration: 1,
        authCode: '',
        customerId: 0,
        registrant: $registrantContact,
        admin: $registrantContact,
        tech: $registrantContact,
        billing: $registrantContact,
        nameserverList: ['ns1.example.com', 'ns2.example.com'],
        privacy: 'true',
        currency: 'USD',
        registerPremium: false,
        couponCode: '',
    );

    // Register the domain
    $result = $client->registerDomain('example.com', $registrationData);
    
    echo "Domain registration successful!\n";
    echo "Domain: " . $result->domainName . "\n";
    echo "Expiration Date: " . date('Y-m-d H:i:s', $result->expirationDate) . "\n";
} catch (Exception $e) {
    // ... handle exception
}
```

### Get account information with `getAccountInfo`

```php
<?php
use Level23\Dynadot\Client;

$apiKey = 'xxx YOUR API KEY xxx';
$apiSecret = 'xxx YOUR API SECRET xxx';

try {
    $client = new Client($apiKey, $apiSecret);
    $accountInfo = $client->getAccountInfo();
    print_r($accountInfo);
} catch (Exception $e) {
    echo $e->getMessage();
}
```

This returns a `Level23\Dynadot\Dto\AccountInfo` object with your account details.

## Available Methods

The following methods are available in the `Client` class:

- `getDomainInfo(string $domainName)` - Get detailed information about a specific domain
- `getDomainList()` - Get a list of all domains in your account
- `setNameservers(string $domainName, array $nameservers)` - Set nameservers for a domain
- `getContactInfo(int $contactId)` - Get contact information by ID
- `getContactList()` - Get a list of all contacts in your account
- `setRenewOption(string $domain, string $renewOption)` - Set renewal option for a domain
- `search(string $domain, bool $showPrice = false, string $currency = 'USD')` - Search for domain availability
- `bulkSearch(array $domains)` - Search for multiple domains at once
- `registerDomain(string $domainName, DomainRegistrationRequest $registrationData)` - Register a new domain
- `getAccountInfo()` - Get information about the authenticated Dynadot account

## Error Handling

The library throws specific exceptions for different error types:

- `Level23\Dynadot\Exception\ApiException` - When the API returns an error response
- `Level23\Dynadot\Exception\NetworkException` - When there's a network communication error
- `Level23\Dynadot\Exception\AuthenticationException` - When authentication fails
- `Level23\Dynadot\Exception\ValidationException` - When request validation fails
- `Level23\Dynadot\Exception\NotFoundException` - When a resource is not found

# FAQ

## I keep getting timeouts!

Make sure your IP address is whitelisted in the Dynadot backend. It can take a while (up to 1 hour) before 
the IP address is whitelisted.

## I am banned from the Dynadot API!

The Dynadot API only allows 1 API call at the same time. It's not allowed to do concurrent API calls. 
If you do request multiple API calls at the same time you can be banned. The ban will be for 10 to 15 minutes.<br />
_Information received via dynadot chat_

## What's the difference between API Key and API Secret?

The API Key is your public identifier, while the API Secret is used to sign your requests for authentication. Both are required for the API to work properly.