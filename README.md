# dynadot-api
Unofficial implementation for the advanced Dynadot domain API

Please note, this is a beta API implementation, based on the API description at
https://www.dynadot.com/domain/api3.html

Due to the somewhat limited documentation of possible error responses, error handling is currently
done best by checking if the StatusCode/ResponseCode (sadly, the naming of this isn't consistent,
see the Dynadot API3 documentation for more info) equals zero, and adding a try/catch block around
each API call.

Only a limited set of features are currently implemented, and the exact methods and parameters
available on this API may change in the future.

## License
See the file LICENSE for more information.

## Example usage
Note, in the below examples no use statements are used, but you'll likely want to use these in your
own code, e.g.:

    use Level23\Dynadot\DynadotApi;
    use Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain;
    ...more use statements here...

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