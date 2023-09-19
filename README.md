# Dynadot-API

[![Build](https://github.com/level23/dynadot-api/actions/workflows/build.yml/badge.svg)](https://github.com/level23/dynadot-api/actions/workflows/build.yml)
[![Code Coverage](https://scrutinizer-ci.com/g/level23/dynadot-api/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/level23/dynadot-api/?branch=master)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)
[![Packagist Version](https://img.shields.io/packagist/v/level23/dynadot-api.svg)](https://packagist.org/packages/level23/dynadot-api)
[![Total Downloads](https://img.shields.io/packagist/dt/level23/dynadot-api.svg)](https://packagist.org/packages/level23/dynadot-api)
[![Quality Score](https://scrutinizer-ci.com/g/level23/dynadot-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/level23/dynadot-api/?branch=master)
[![Software License](https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg)](LICENSE)


Unofficial implementation for the advanced Dynadot domain API

Please note, this is a beta API implementation, based on the API description at
https://www.dynadot.com/domain/api3.html

Before you can use this API you have to:

  * Get the API key from the dynadot backend
  * Whitelist the IP address where your requests are coming from


By default, we will try to connect to the Dynadoy API for 30 seconds. If that fails, 
an `GuzzleHttp\Exception\ConnectException` is thrown. You probably want to catch these in case if something goes wrong.

Only a limited set of features are currently implemented, and the exact methods and parameters
available on this API may change in the future.

## Installing

Install the latest version with:

```bash
$ composer require level23/dynadot-api
```

## Requirements

To make use if this API you have to run PHP 8.2 or higher.

## Contributing

If you want to help us improve this implementation, just contact us. All help is welcome!
The only requirement for contributing is that all code is 100% covered by unit tests and that they implement the 
PSR standards.

## License

See the file LICENSE for more information.

## Example usage

See below some basic sample usages.

### Getting Domain Details with `getDomainInfo`

```php
<?php
use Level23\Dynadot\DynadotApi;

$apiKey = 'xxx YOUR API KEY xxx';

try {
    $api = new DynadotApi($apiKey);
    print_r($api->getDomainInfo('example.com'));
    
    // process your domain info here
} catch (Exception $e) {
    // ... handle exception
}
```

The returned object will be an instance of `Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain`. 

Example response:
```
Level23\Dynadot\ResultObjects\DomainResponse\Domain Object
(
    [Name] => example.com
    [Expiration] => 1514764799000
    [Registration] => 1291735572000
    [NameServerSettings] => Level23\Dynadot\ResultObjects\DomainResponse\NameServerSettings Object
        (
            [Type] => Name Servers
            [NameServers] => Array
                (
                    [0] => Array
                        (
                            [0] => Level23\Dynadot\ResultObjects\DomainResponse\NameServer Object
                                (
                                    [ServerId] => 1234
                                    [ServerName] => abc.org
                                )

                            [1] => Level23\Dynadot\ResultObjects\DomainResponse\NameServer Object
                                (
                                    [ServerId] => 12346
                                    [ServerName] => abc.com
                                )

                            [2] => Level23\Dynadot\ResultObjects\DomainResponse\NameServer Object
                                (
                                    [ServerId] => 1245
                                    [ServerName] => abc.co.uk
                                )

                            [3] => Level23\Dynadot\ResultObjects\DomainResponse\NameServer Object
                                (
                                    [ServerId] => 1267
                                    [ServerName] => abc.net
                                )

                        )

                )

            [WithAds] => false
        )

    [Whois] => Level23\Dynadot\ResultObjects\DomainResponse\Whois Object
        (
            [Registrant] => 1234
            [Admin] => 1234
            [Technical] => 1234
            [Billing] => 1234
        )

    [Locked] => true
    [Disabled] => false
    [UdrpLocked] => false
    [RegistrantUnverified] => false
    [Hold] => false
    [Privacy] => none
    [isForSale] => false
    [RenewOption] => auto-renew
    [Note] => 
    [Folder] => Level23\Dynadot\ResultObjects\DomainResponse\Folder Object
        (
            [FolderId] => 1234
            [FolderName] => Other
        )

)

```

The `Whois` container will return the contact id's for this specific domain. Full contact details can be fetched with 
this id by using the `getContactInfo` API call.

### List all domains with `getDomainList`

```php
<?php

use Level23\Dynadot\DynadotApi;

$apiKey = 'xxx YOUR API KEY xxx';


try {
    $api = new DynadotApi($apiKey);
    $list = $api->getDomainList();

    print_r( $list );
} catch (Exception $e) {
    // ... handle exception
}
```

This will return an array of `Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain` objects. An exception will be 
thrown when anything went wrong. 


### Set nameservers for a domain with `setNameserversForDomain`

```php
<?php

use Level23\Dynadot\DynadotApi;

$apiKey = 'xxx YOUR API KEY xxx';

try {
    $api = new DynadotApi($apiKey);
    $api->setNameserversForDomain('exmple.com', ['ns01.example.com', 'ns2.example.net', 'ns03.example.org']);
    // ...
} catch (Exception $e) {
    // ... handle exception
}
```
The `setNameserversForDomain` method will by default not give a response. An exception will be thrown when something 
went wrong.


### Retrieving contact info with `getContactInfo`

```php
<?php
use Level23\Dynadot\DynadotApi;

$apiKey = 'xxx YOUR API KEY xxx';

try {
    $api = new DynadotApi($apiKey);
    print_r($api->getContactInfo(1234)); // 1234 = the contact id, for example returned by the getDomainInfo call.
} catch (Exception $e) {
    echo $e->getMessage();
}
```

An exception will be thrown when something went wrong.

Example response:
```
Level23\Dynadot\ResultObjects\GetContactResponse\Contact Object
(
    [ContactId] => 12345
    [Organization] => org
    [Name] => name
    [Email] => example@example.com
    [PhoneCc] => 0
    [PhoneNum] => phone number
    [FaxCc] => example faxcc
    [FaxNum] => example faxnum
    [Address1] => address1
    [Address2] => address2
    [City] => city
    [State] => state
    [ZipCode] => zipcode
    [Country] => country
)
```

### Set renew option with `setRenewOption`

```php
<?php

use Level23\Dynadot\DynadotApi;

$apiKey = 'xxx YOUR API KEY xxx';

try {
    $api = new DynadotApi($apiKey);
    $api->setRenewOption('example.com', 'auto');
    // ...
} catch (Exception $e) {
    // ... handle exception
}
```
The `setRenewOption` let's you set the renewal setting for a domain. Values of for the second 
argument ($renewOption) van be "donot", "auto", "reset". The method will return `true` when 
the renew option is set successfully.

# FAQ

## I keep getting timeouts!

Make sure your IP address is whitelisted in the Dynadot backend. It can take a while (up to 1 hour) before 
the IP address is whitelisted.

## I am banned from the Dynadot API!

The Dynadot API only allows 1 API call at the same time. Its not allowed to do concurrent API calls. 
If you do request multiple API calls at the same time you can be banned. The ban will be for 10 a 15 minutes.<br />
_Information received via dynadot chat_