<?php

namespace Level23\Dynadot;

use GuzzleHttp\Client;
use Level23\Dynadot\Exception\ApiHttpCallFailedException;
use Level23\Dynadot\Exception\ApiLimitationExceededException;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\DomainInfoResponse;
use Level23\Dynadot\ResultObjects\DomainResponse;
use Level23\Dynadot\ResultObjects\GeneralResponse;
use Level23\Dynadot\ResultObjects\GetContactResponse;
use Level23\Dynadot\ResultObjects\ListDomainInfoResponse;
use Level23\Dynadot\ResultObjects\SetNsResponse;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

/**
 * Class DynadotApi
 * @package Level23\Dynadot
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
     * Dynadot's API key we should use for HTTP calls.
     * @var string
     */
    protected $apiKey;

    /**
     * Logger for writing debug info
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Changes boolean values like "no" and "yes" into false and true.
     * @param \Sabre\Xml\Reader $reader
     * @return bool
     * @throws DynadotApiException
     */
    protected $booleanDeserializer;
    /**
     * Return the contact id
     * @param Reader $reader
     * @return int
     */
    protected $contactIdDeserializer;

    /**
     * DynadotApi constructor.
     *
     * @param string $apiKey The API key we should use while communicating with the Dynadot API.
     * @param LoggerInterface $logger
     * @internal param $Logger
     */
    public function __construct($apiKey, LoggerInterface $logger = null)
    {
        $this->setApiKey($apiKey);
        $this->logger = $logger;

        /**
         * Set the default guzzle options
         */
        $this->setGuzzleOptions([
            'max' => 5,
            'referer' => false,
            'protocols' => ['https'],
            'connect_timeout' => 30
        ]);

        /**
         * Changes boolean values like "no" and "yes" into false and true.
         * @param \Sabre\Xml\Reader $reader
         * @return bool
         * @throws DynadotApiException
         */
        $this->booleanDeserializer = function (Reader $reader) {
            $value = $reader->parseInnerTree();

            if ($value != 'yes' && $value != 'no') {
                throw new DynadotApiException('Error, received incorrect boolean value ' . $value);
            }

            return $value == 'no' ? false : true;
        };

        /**
         * Return the contact id
         * @param Reader $reader
         * @return int
         */
        $this->contactIdDeserializer = function (Reader $reader) {
            $children = $reader->parseInnerTree();
            return $children[0]['value'];
        };
    }

    /**
     * @param array $optionsArray
     */
    public function setGuzzleOptions($optionsArray)
    {
        $this->guzzleOptions = $optionsArray;
    }

    /**
     * Get info about a domain
     *
     * @param $domain
     * @return DomainResponse\Domain
     * @throws DynadotApiException
     */
    public function getDomainInfo($domain)
    {
        $this->log(LogLevel::INFO, 'Retrieve info for domain: ' . $domain);

        $requestData = [
            'domain' => $domain,
            'command' => 'domain_info'
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        // start parsing XML data using Sabre
        $sabreService = new Service();

        // set mapping
        $sabreService->elementMap = [
            '{}NameServers' => function (Reader $reader) {

                $nameservers = [];
                $id = '';

                $children = $reader->parseInnerTree();

                foreach ($children as $child) {
                    if ($child['name'] == '{}ServerId') {
                        $id = $child['value'];
                    } elseif ($child['name'] == '{}ServerName') {
                        if (!empty($id) && !empty($child['value'])) {
                            $nameserver = new DomainResponse\NameServer();

                            $nameserver->ServerId = $id;
                            $nameserver->ServerName = $child['value'];

                            $nameservers[] = $nameserver;
                        }
                        $id = null;
                    }
                }

                return $nameservers;
            },
            '{}Registrant' => $this->contactIdDeserializer,
            '{}Admin' => $this->contactIdDeserializer,
            '{}Technical' => $this->contactIdDeserializer,
            '{}Billing' => $this->contactIdDeserializer,
            '{}isForSale' => $this->booleanDeserializer,
            '{}Hold' => $this->booleanDeserializer,
            '{}RegistrantUnverified' => $this->booleanDeserializer,
            '{}UdrpLocked' => $this->booleanDeserializer,
            '{}Disabled' => $this->booleanDeserializer,
            '{}Locked' => $this->booleanDeserializer,
            '{}WithAds' => $this->booleanDeserializer
        ];

        // map certain values to objects
        $sabreService->mapValueObject('{}DomainInfoResponse', DomainInfoResponse\DomainInfoResponse::class);
        $sabreService->mapValueObject('{}DomainInfoResponseHeader', DomainInfoResponse\DomainInfoResponseHeader::class);
        $sabreService->mapValueObject('{}DomainInfoContent', DomainInfoResponse\DomainInfoContent::class);
        $sabreService->mapValueObject('{}Domain', DomainResponse\Domain::class);
        $sabreService->mapValueObject('{}NameServerSettings', DomainResponse\NameServerSettings::class);
        $sabreService->mapValueObject('{}Whois', DomainResponse\Whois::class);
        $sabreService->mapValueObject('{}Folder', DomainResponse\Folder::class);
        $sabreService->mapValueObject('{}Response', GeneralResponse\Response::class);
        $sabreService->mapValueObject('{}ResponseHeader', GeneralResponse\ResponseHeader::class);

        $this->log(LogLevel::DEBUG, 'Start parsing response XML');

        // parse the data
        $resultData = $sabreService->parse($response);

        // General error, like incorrect api key
        if ($resultData instanceof GeneralResponse\Response) {
            $code = $resultData->ResponseHeader->ResponseCode;
            if ($code != GeneralResponse\ResponseHeader::RESPONSECODE_OK) {
                throw new DynadotApiException($resultData->ResponseHeader->Error);
            }
        }

        if (!$resultData instanceof DomainInfoResponse\DomainInfoResponse) {
            throw new DynadotApiException('We failed to parse the response');
        }

        /**
         * Check if the API call was successful. If not, return the error
         */
        $code = $resultData->DomainInfoResponseHeader->SuccessCode;
        if ($code != DomainInfoResponse\DomainInfoResponseHeader::SUCCESSCODE_OK) {
            throw new DynadotApiException($resultData->DomainInfoResponseHeader->Error);
        }

        $this->log(LogLevel::DEBUG, 'Returning domain info');

        // Here we know our API call was succesful, return the domain info.
        return $resultData->DomainInfoContent->Domain;
    }

    /**
     * Log a message to our logger, if we have any.
     * @param $level
     * @param $message
     */
    protected function log($level, $message)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message);
        }
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
        $this->log(LogLevel::DEBUG, 'Perform raw call: ' . var_export($requestData, true));

        // transform the request data into a valid query string
        $requestDataHttp = http_build_query($requestData);

        // spawn Guzzle
        $client = new Client($this->guzzleOptions);

        $url = self::DYNADOT_API_URL .
            '?key=' . urlencode($this->getApiKey()) .
            ($requestDataHttp ? '&' . $requestDataHttp : '');

        $this->log(LogLevel::DEBUG, 'Start new guzzle request with URL: ' . $url);

        // start a request with out API key and optionally our request data
        $response = $client->request('GET', $url);

        $this->log(LogLevel::DEBUG, 'Received response with status code ' . $response->getStatusCode());

        // if we did not get a HTTP 200 response, our HTTP call failed (which is different from a failed API call)
        if ($response->getStatusCode() != 200) {
            $this->log(LogLevel::ALERT, 'Received wrong HTTP status code: ' . $response->getStatusCode());
            // not ok
            throw new ApiHttpCallFailedException(
                'HTTP API call failed, expected 200 status, got ' . $response->getStatusCode()
            );
        }

        // Return the response body (which is a stream coming from Guzzle).
        // Sabre XML semi-handles streams (it will just get the contents of the stream using stream_get_contents) so
        // this should work! ;)
        return $response->getBody();
    }

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
     * Set nameservers for a domain (max 13). An exception will be thrown in case of an error.
     *
     * @param string $domain The domain where to set the nameservers for.
     * @param array $nameservers
     * @throws ApiLimitationExceededException
     * @throws DynadotApiException
     */
    public function setNameserversForDomain($domain, array $nameservers)
    {
        $this->log(LogLevel::DEBUG, 'Set ' . sizeof($nameservers) . ' nameservers for domain ' . $domain);
        $requestData = [
            'command' => 'set_ns',
            'domain' => $domain
        ];

        if (sizeof($nameservers) > 13) {
            // index starts at 0, so we should check if the index is greater than 12 (which is 13 nameservers)
            throw new ApiLimitationExceededException(
                'Can not define more than 13 nameservers through the API'
            );
        }

        $idx = 0;
        // check if there are more than 13 nameservers defined
        foreach ($nameservers as $nameserver) {
            $requestData['ns' . $idx++] = $nameserver;
        }

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        $this->log(LogLevel::DEBUG, 'API call execured, parsing response...');

        // start parsing XML data using Sabre
        $sabreService = new Service();

        // map certain values to objects
        $sabreService->mapValueObject('{}SetNsResponse', SetNsResponse\SetNsResponse::class);
        $sabreService->mapValueObject('{}SetNsHeader', SetNsResponse\SetNsHeader::class);
        $sabreService->mapValueObject('{}Response', GeneralResponse\Response::class);
        $sabreService->mapValueObject('{}ResponseHeader', GeneralResponse\ResponseHeader::class);

        // parse the data
        $resultData = $sabreService->parse($response);

        // General error, like incorrect api key
        if ($resultData instanceof GeneralResponse\Response) {
            $code = $resultData->ResponseHeader->ResponseCode;
            if ($code != GeneralResponse\ResponseHeader::RESPONSECODE_OK) {
                throw new DynadotApiException($resultData->ResponseHeader->Error);
            }
        }

        if (!$resultData instanceof SetNsResponse\SetNsResponse) {
            throw new DynadotApiException('We failed to parse the response');
        }

        /**
         * Check if the API call was successful. If not, return the error
         */
        $code = $resultData->SetNsHeader->SuccessCode;
        if ($code != SetNsResponse\SetNsHeader::SUCCESSCODE_OK) {
            throw new DynadotApiException($resultData->SetNsHeader->Error);
        }

        $this->log(LogLevel::DEBUG, 'Received correct response. Everything is ok!');
    }

    /**
     * List all domains in the account. We will return an array with Domain objects
     * @return DomainResponse\Domain[]
     * @throws DynadotApiException
     */
    public function getDomainList()
    {
        $this->log(LogLevel::DEBUG, 'Start retrieving all domains');
        $requestData = [
            'command' => 'list_domain',
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        $this->log(LogLevel::DEBUG, 'Start parsing response XML');

        // start parsing XML data using Sabre
        $sabreService = new Service();

        // set mapping
        $sabreService->elementMap = [
            '{}NameServers' => function (Reader $reader) {

                $nameservers = [];
                $id = '';

                $children = $reader->parseInnerTree();

                foreach ($children as $child) {
                    if ($child['name'] == '{}ServerId') {
                        $id = $child['value'];
                    } elseif ($child['name'] == '{}ServerName') {
                        if (!empty($id) && !empty($child['value'])) {
                            $nameserver = new DomainResponse\NameServer();
                            ;
                            $nameserver->ServerId = $id;
                            $nameserver->ServerName = $child['value'];

                            $nameservers[] = $nameserver;
                        }
                        $id = null;
                    }
                }

                return $nameservers;
            },
            '{}DomainInfoList' => function (Reader $reader) {
                $domains = [];

                $tree = $reader->parseInnerTree();
                foreach ($tree as $item) {
                    $domains[] = $item['value'][0]['value'];
                }

                return $domains;
            },
            '{}Registrant' => $this->contactIdDeserializer,
            '{}Admin' => $this->contactIdDeserializer,
            '{}Technical' => $this->contactIdDeserializer,
            '{}Billing' => $this->contactIdDeserializer,
            '{}isForSale' => $this->booleanDeserializer,
            '{}Hold' => $this->booleanDeserializer,
            '{}RegistrantUnverified' => $this->booleanDeserializer,
            '{}UdrpLocked' => $this->booleanDeserializer,
            '{}Disabled' => $this->booleanDeserializer,
            '{}Locked' => $this->booleanDeserializer,
            '{}WithAds' => $this->booleanDeserializer,
        ];

        // map certain values to objects
        $sabreService->mapValueObject('{}ListDomainInfoResponse', ListDomainInfoResponse\ListDomainInfoResponse::class);
        $sabreService->mapValueObject('{}ListDomainInfoHeader', ListDomainInfoResponse\ListDomainInfoHeader::class);
        $sabreService->mapValueObject('{}ListDomainInfoContent', ListDomainInfoResponse\ListDomainInfoContent::class);
        $sabreService->mapValueObject('{}Domain', DomainResponse\Domain::class);
        $sabreService->mapValueObject('{}NameServerSettings', DomainResponse\NameServerSettings::class);
        $sabreService->mapValueObject('{}Whois', DomainResponse\Whois::class);
        $sabreService->mapValueObject('{}Folder', DomainResponse\Folder::class);
        $sabreService->mapValueObject('{}Response', GeneralResponse\Response::class);
        $sabreService->mapValueObject('{}ResponseHeader', GeneralResponse\ResponseHeader::class);

        // parse the data
        $resultData = $sabreService->parse($response);

        // General error, like incorrect api key
        if ($resultData instanceof GeneralResponse\Response) {
            $code = $resultData->ResponseHeader->ResponseCode;
            if ($code != GeneralResponse\ResponseHeader::RESPONSECODE_OK) {
                throw new DynadotApiException($resultData->ResponseHeader->Error);
            }
        }

        if (!$resultData instanceof ListDomainInfoResponse\ListDomainInfoResponse) {
            throw new DynadotApiException('We failed to parse the response');
        }

        /**
         * Check if the API call was successful. If not, return the error
         */
        $code = $resultData->ListDomainInfoHeader->ResponseCode;
        if ($code != ListDomainInfoResponse\ListDomainInfoHeader::RESPONSECODE_OK) {
            throw new DynadotApiException($resultData->ListDomainInfoHeader->Error);
        }

        return $resultData->ListDomainInfoContent->DomainInfoList;
    }

    /**
     * Get contact information for a specific contact ID
     *
     * @param int $contactId The contact ID we should request
     * @return GetContactResponse\Contact
     * @throws DynadotApiException
     */
    public function getContactInfo($contactId)
    {
        $this->log(LogLevel::DEBUG, 'Fetch contact details for id ' . $contactId);

        $requestData = [
            'command' => 'get_contact',
            'contact_id' => $contactId
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        $this->log(LogLevel::DEBUG, 'Start parsing result');
        
        // start parsing XML data using Sabre
        $sabreService = new Service();

        // map certain values to objects
        $sabreService->mapValueObject('{}GetContactResponse', GetContactResponse\GetContactResponse::class);
        $sabreService->mapValueObject('{}GetContactHeader', GetContactResponse\GetContactHeader::class);
        $sabreService->mapValueObject('{}GetContactContent', GetContactResponse\GetContactContent::class);
        $sabreService->mapValueObject('{}Contact', GetContactResponse\Contact::class);
        $sabreService->mapValueObject('{}Response', GeneralResponse\Response::class);
        $sabreService->mapValueObject('{}ResponseHeader', GeneralResponse\ResponseHeader::class);

        // parse the data
        $resultData = $sabreService->parse($response);

        // General error, like incorrect api key
        if ($resultData instanceof GeneralResponse\Response) {
            $code = $resultData->ResponseHeader->ResponseCode;
            if ($code != GeneralResponse\ResponseHeader::RESPONSECODE_OK) {
                throw new DynadotApiException($resultData->ResponseHeader->Error);
            }
        }

        if (!$resultData instanceof GetContactResponse\GetContactResponse) {
            throw new DynadotApiException('We failed to parse the response');
        }

        /**
         * Check if the API call was successful. If not, return the error
         */
        $code = $resultData->GetContactHeader->ResponseCode;
        if ($code != GetContactResponse\GetContactHeader::RESPONSECODE_OK) {
            throw new DynadotApiException($resultData->GetContactHeader->Error);
        }

        return $resultData->GetContactContent->Contact;
    }
}
