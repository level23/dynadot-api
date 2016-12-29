<?php

namespace Level23\Dynadot;

use Level23\Dynadot\Exception\ApiHttpCallFailedException;
use Level23\Dynadot\Exception\ApiLimitationExceededException;

class DynadotApi
{

    const DYNADOT_API_URL = 'https://api.dynadot.com/api3.xml';

    /**
     * This options array is used by Guzzle.
     *
     * We currently use it to set the Mock Handler in unit testing.
     *
     * @var array
     */
    protected $guzzleOptions = [];

    /**
     * @param array $optionsArray
     */
    public function setGuzzleOptions($optionsArray)
    {
        $this->guzzleOptions = $optionsArray;
    }

    /**
     * Dynadot's API key we should use for HTTP calls.
     * @var string
     */
    protected $apiKey;

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * DynadotApi constructor.
     *
     * @param string $apiKey The API key we should use while communicating with the Dynadot API.
     */
    public function __construct($apiKey)
    {
        $this->setApiKey($apiKey);
    }

    /**
     * Performs the actual API call (internal method)
     *
     * @param array $requestData
     * @throws ApiHttpCallFailedException
     * @return \Psr\Http\Message\StreamInterface
     */
    protected function performRawApiCall(array $requestData)
    {
        // transform the request data into a valid query string
        $requestDataHttp = http_build_query($requestData);

        // spawn Guzzle
        $client = new \GuzzleHttp\Client($this->guzzleOptions);

        // start a request with out API key and optionally our request data
        $res = $client->request(
            'GET',
            self::DYNADOT_API_URL . '?key=' . urlencode($this->getApiKey()) .
            ($requestDataHttp ? '&' . $requestDataHttp : '')
        );

        // if we did not get a HTTP 200 response, our HTTP call failed (which is different from a failed API call)
        if ($res->getStatusCode() != 200) {
            // not ok
            throw new ApiHttpCallFailedException(
                'HTTP API call failed, expected 200 status, got ' . $res->getStatusCode()
            );
        }

        // Return the response body (which is a stream coming from Guzzle).
        // Sabre XML semi-handles streams (it will just get the contents of the stream using stream_get_contents) so
        // this should work! ;)
        return $res->getBody();
    }

    /**
     * Get info about a domain
     *
     * @param $domain
     * @return array
     */
    public function performDomainInfo($domain)
    {
        $requestData = [
            'domain' => $domain,
            'command' => 'domain_info'
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        // start parsing XML data using Sabre
        $sabreService = new \Sabre\Xml\Service();

        // set mapping
        $sabreService->elementMap = [
            '{}DomainInfoResponse' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            },
            '{}DomainInfoContent' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            },
        ];

        // map certain values to objects
        $sabreService->mapValueObject(
            '{}Domain',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Domain::class
        );
        $sabreService->mapValueObject(
            '{}NameServerSettings',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\NameServerSettings::class
        );
        $sabreService->mapValueObject(
            '{}DomainInfoResponseHeader',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\DomainInfoResponseHeader::class
        );
        $sabreService->mapValueObject(
            '{}Whois',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Whois::class
        );
        $sabreService->mapValueObject(
            '{}Registrant',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Registrant::class
        );
        $sabreService->mapValueObject(
            '{}Admin',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Admin::class
        );
        $sabreService->mapValueObject(
            '{}Technical',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Technical::class
        );
        $sabreService->mapValueObject(
            '{}Billing',
            \Level23\Dynadot\ResultObjects\DomainInfoResponses\Billing::class
        );

        // parse the data, we are expecting a DomainInfoResponse root node
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $resultData = $sabreService->expect('DomainInfoResponse', $response);

        return $resultData;
    }

    /**
     * Set nameservers for a domain
     *
     * @param $domain
     * @param array $nameservers
     * @return array
     * @throws ApiLimitationExceededException
     */
    public function performSetNs($domain, array $nameservers)
    {
        $requestData = [
            'command' => 'set_ns',
            'domain' => $domain
        ];

        // check if there are more than 13 nameservers defined
        foreach ($nameservers as $idx => $nameserver) {
            if ($idx > 12) {
                // index starts at 0, so we should check if the index is greater than 12 (which is 13 nameservers)
                throw new ApiLimitationExceededException(
                    'Can not define more than 13 nameservers through the API'
                );
            }
            $requestData['ns' . $idx] = $nameserver;
        }

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        // start parsing XML data using Sabre
        $sabreService = new \Sabre\Xml\Service();

        // set mapping
        $sabreService->elementMap = [
            '{}SetNsResponse' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            },
        ];

        // map certain values to objects
        $sabreService->mapValueObject(
            '{}SetNsHeader',
            \Level23\Dynadot\ResultObjects\SetNsResponses\SetNsHeader::class
        );

        // parse the data, we are expecting a SetNsResponse root node
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $resultData = $sabreService->expect('SetNsResponse', $response);

        return $resultData;
    }

    /**
     * List all domains in the account
     *
     * @return array
     */
    public function performListDomain()
    {
        $requestData = [
            'command' => 'list_domain',
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        // start parsing XML data using Sabre
        $sabreService = new \Sabre\Xml\Service();

        // set mapping
        $sabreService->elementMap = [
            '{}ListDomainInfoResponse' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            },
            '{}ListDomainInfoContent' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            },
            '{}DomainInfoList' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\repeatingElements($reader, 'DomainInfo');
            },
            '{}DomainInfo' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            },
        ];

        // map certain values to objects
        $sabreService->mapValueObject(
            '{}ListDomainInfoHeader',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\ListDomainInfoHeader::class
        );
        $sabreService->mapValueObject(
            '{}Domain',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Domain::class
        );
        $sabreService->mapValueObject(
            '{}NameServerSettings',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\NameServerSettings::class
        );
        $sabreService->mapValueObject(
            '{}DomainInfoResponseHeader',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\DomainInfoResponseHeader::class
        );
        $sabreService->mapValueObject(
            '{}Whois',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Whois::class
        );
        $sabreService->mapValueObject(
            '{}Registrant',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Registrant::class
        );
        $sabreService->mapValueObject(
            '{}Admin',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Admin::class
        );
        $sabreService->mapValueObject(
            '{}Technical',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Technical::class
        );
        $sabreService->mapValueObject(
            '{}Billing',
            \Level23\Dynadot\ResultObjects\ListDomainInfoResponses\Billing::class
        );

        // parse the data, we are expecting a ListDomainInfoResponse root node
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $resultData = $sabreService->expect('ListDomainInfoResponse', $response);

        return $resultData;
    }

    /**
     * Get contact information for a specific contact ID
     *
     * @param int $contactId    The contact ID we should request
     * @return array
     */
    public function performGetContact($contactId)
    {
        $requestData = [
            'command' => 'get_contact',
            'contact_id' => $contactId
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        // start parsing XML data using Sabre
        $sabreService = new \Sabre\Xml\Service();

        // set mapping
        $sabreService->elementMap = [
            '{}GetContactResponse' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            },
            '{}GetContactContent' => function (\Sabre\Xml\Reader $reader) {
                return \Sabre\Xml\Deserializer\keyValue($reader);
            }
        ];

        // map certain values to objects
        $sabreService->mapValueObject(
            '{}GetContactHeader',
            \Level23\Dynadot\ResultObjects\GetContactResponses\GetContactHeader::class
        );
        $sabreService->mapValueObject(
            '{}Contact',
            \Level23\Dynadot\ResultObjects\GetContactResponses\Contact::class
        );

        // parse the data, we are expecting a GetContactResponse root node
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $resultData = $sabreService->expect('GetContactResponse', $response);

        return $resultData;
    }
}
