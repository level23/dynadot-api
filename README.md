[![Build Status](https://travis-ci.org/level23/dynadot-api.svg?branch=master)](https://travis-ci.org/level23/dynadot-api)

# dynadot-api
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

## NOTE

This API is still in development and should only be used at your own risk!

## Contributing

If you want to help us improve this implementation, just contact us. All help is welcome!

## License
See the file LICENSE for more information.

## Example usage
Note, in the below examples no use statements are used, but you'll likely want to use these in your
own code, e.g.:

    use Level23\Dynadot\DynadotApi;
    use Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain;
    ...more use statements here...

### Getting Domain Details

```php
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

The returned Domain Object will be an instance of `Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain`. 



Setting nameservers:

    $dynadotApi = new \Level23\Dynadot\DynadotApi('\_API\_KEY\_');
    try {
        $dynadotApi->performSetNs('example.com', [
            'ns01.example.com',
            'ns02.example.com'
        ]);
    }
    catch (Exception $e) {
        /* ...handle exception here... */
    }
    
Querying domain expiration:

    $dynadotApi = new \Level23\Dynadot\DynadotApi('\_API\_KEY\_');
    try {
        $result = $dynadotApi->performDomainInfo('example.com');
        if (
            isset($result['{}DomainInfoContent']['{}Domain']) &&
            $result['{}DomainInfoContent']['{}Domain'] instanceof
                \Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain
        ) {
            $domain = $result['{}DomainInfoContent']['{}Domain'];
            print date('Y-m-d H:i:s', round($domain->Expiration / 1000)) . PHP_EOL;
        } else {
            /* malformed response received from API? */
            /* handle error case here */
        }
    } catch (Exception $e) {
        /* ...handle exception here... */
    }
    
Querying whois contacts:

    $dynadotApi = new \Level23\Dynadot\DynadotApi('\_API\_KEY\_');
    try {
        $result = $dynadotApi->performDomainInfo('example.com');
        if (
            isset($result['{}DomainInfoResponseHeader']) &&
            $result['{}DomainInfoResponseHeader'] instanceof
                \Level23\Dynadot\ResultObjects\DomainInfoResponses\DomainInfoResponseHeader &&
            $result['{}DomainInfoResponseHeader']->SuccessCode == 0 &&
            isset($result['{}DomainInfoContent']['{}Domain']) &&
            $result['{}DomainInfoContent']['{}Domain'] instanceof
                \Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain
        ) {
            /**
             * @var \Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain $domain
             */
            $domain = $result['{}DomainInfoContent']['{}Domain'];
            
            $contactResponse = $api->performGetContact($domain->Whois->Registrant->ContactId);
            if (
                isset($contactResponse['{}GetContactHeader']) &&
                $contactResponse['{}GetContactHeader'] instanceof
                    \Level23\Dynadot\ResultObjects\GetContactResponses\GetContactHeader &&
                $contactResponse['{}GetContactHeader']->ResponseCode == 0 &&
                isset($contactResponse['{}GetContactContent']['{}Contact']) &&
                $contactResponse['{}GetContactContent']['{}Contact'] instanceof
                    \Level23\Dynadot\ResultObjects\GetContactResponses\Contact
            ) {
                /**
                 * @var Level23\Dynadot\ResultObjects\GetContactResponses\Contact $contact
                 */
                $contact = $contactResponse['{}GetContactContent']['{}Contact'];
                $this->line('Name: ' . $contact->Name);
                $this->line('Organization: ' . $contact->Name);
                $this->line('Address1: ' . $contact->Address1);
                $this->line('Address2: ' . $contact->Address2);
                $this->line('ZipCode: ' . $contact->ZipCode);
                $this->line('City: ' . $contact->City);
                $this->line('Country: ' . $contact->Country);
            } else {
                /* malformed response received from API? */
                /* handle error case here */
            }
        } else {
            /* malformed response received from API? */
            /* handle error case here */
        }
    } catch (Exception $e) {
        /* ... handle exception here ... */
    }

    
# FAQ

## I keep getting timeouts!

Make sure your IP address is whitelisted in the Dynadot backend. It can take a while (up to 1 hour) before 
the IP address is whitelisted.

