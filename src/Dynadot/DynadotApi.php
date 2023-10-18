<?php

namespace Level23\Dynadot;

use Psr\Log\LogLevel;
use Sabre\Xml\Reader;
use GuzzleHttp\Client;
use Sabre\Xml\Service;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\StreamInterface;
use Level23\Dynadot\ResultObjects\SetNsResponse;
use Level23\Dynadot\ResultObjects\DomainResponse;
use Level23\Dynadot\Exception\DynadotApiException;
use Level23\Dynadot\ResultObjects\GeneralResponse;
use Level23\Dynadot\ResultObjects\DomainInfoResponse;
use Level23\Dynadot\ResultObjects\GetContactResponse;
use Level23\Dynadot\Exception\ApiHttpCallFailedException;
use Level23\Dynadot\ResultObjects\ListDomainInfoResponse;
use Level23\Dynadot\Exception\ApiLimitationExceededException;
use Level23\Dynadot\ResultObjects\RenewOptionResponse\SetRenewOptionHeader;
use Level23\Dynadot\ResultObjects\RenewOptionResponse\SetRenewOptionResponse;

/**
 * Class DynadotApi
 *
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
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Logger for writing debug info
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * Changes boolean values like "no" and "yes" into false and true.
     *
     * @return bool
     * @throws DynadotApiException
     * @var \Closure
     */
    protected $booleanDeserializer;

    /**
     * Return the contact id
     *
     * @param Reader $reader
     *
     * @return int
     * @var \Closure
     */
    protected $contactIdDeserializer;

    /**
     * DynadotApi constructor.
     *
     * @param string                        $apiKey The API key we should use while communicating with the Dynadot API.
     * @param \Psr\Log\LoggerInterface|null $logger
     *
     * @internal param $Logger
     */
    public function __construct(string $apiKey, LoggerInterface $logger = null)
    {
        $this->setApiKey($apiKey);
        $this->logger = $logger;

        /**
         * Set the default guzzle options
         */
        $this->setGuzzleOptions([
            'max'             => 5,
            'referer'         => false,
            'protocols'       => ['https'],
            'connect_timeout' => 30,
        ]);

        /**
         * Changes boolean values like "no" and "yes" into false and true.
         *
         * @param \Sabre\Xml\Reader $reader
         *
         * @return bool
         * @throws \Level23\Dynadot\Exception\DynadotApiException
         * @throws \Sabre\Xml\LibXMLException
         * @throws \Sabre\Xml\ParseException
         */
        $this->booleanDeserializer = function (Reader $reader) {
            $value = $reader->parseInnerTree();
            if (is_string($value)) {
                $value = strtolower($value);
            }

            if ($value != 'yes' && $value != 'no') {
                throw new DynadotApiException('Error, received incorrect boolean value ' . var_export($value, true));
            }

            return ($value !== 'no');
        };

        /**
         * Return the contact id
         *
         * @param Reader $reader
         *
         * @return int
         * @throws \Sabre\Xml\LibXMLException
         * @throws \Sabre\Xml\ParseException
         */
        $this->contactIdDeserializer = function (Reader $reader) {
            $children = (array)$reader->parseInnerTree();

            return $children[0]['value'];
        };
    }

    /**
     * @param array $optionsArray
     */
    public function setGuzzleOptions(array $optionsArray): void
    {
        $this->guzzleOptions = $optionsArray;
    }

    /**
     * Get info about a domain
     *
     * @param string $domain
     *
     * @return DomainResponse\Domain
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     * @throws \Level23\Dynadot\Exception\DynadotApiException
     * @throws \Sabre\Xml\ParseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDomainInfo(string $domain): DomainResponse\Domain
    {
        $this->log(LogLevel::INFO, 'Retrieve info for domain: ' . $domain);

        $requestData = [
            'domain'  => $domain,
            'command' => 'domain_info',
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        // start parsing XML data using Sabre
        $sabreService = new Service();

        // set mapping
        $sabreService->elementMap = [
            '{}Registrant'           => $this->contactIdDeserializer,
            '{}Admin'                => $this->contactIdDeserializer,
            '{}Technical'            => $this->contactIdDeserializer,
            '{}Billing'              => $this->contactIdDeserializer,
            '{}isForSale'            => $this->booleanDeserializer,
            '{}Hold'                 => $this->booleanDeserializer,
            '{}RegistrantUnverified' => $this->booleanDeserializer,
            '{}UdrpLocked'           => $this->booleanDeserializer,
            '{}Disabled'             => $this->booleanDeserializer,
            '{}Locked'               => $this->booleanDeserializer,
            '{}WithAds'              => $this->booleanDeserializer,
        ];

        // map certain values to objects
        $sabreService->mapValueObject('{}DomainInfoResponse', DomainInfoResponse\DomainInfoResponse::class);
        $sabreService->mapValueObject('{}DomainInfoHeader', DomainInfoResponse\DomainInfoHeader::class);
        $sabreService->mapValueObject('{}DomainInfoContent', DomainInfoResponse\DomainInfoContent::class);
        $sabreService->mapValueObject('{}Domain', DomainResponse\Domain::class);
        $sabreService->mapValueObject('{}NameServerSettings', DomainResponse\NameServerSettings::class);
        $sabreService->mapValueObject('{}NameServer', DomainResponse\NameServer::class);
        $sabreService->mapValueObject('{}Whois', DomainResponse\Whois::class);
        $sabreService->mapValueObject('{}Folder', DomainResponse\Folder::class);
        $sabreService->mapValueObject('{}Response', GeneralResponse\Response::class);
        $sabreService->mapValueObject('{}ResponseHeader', GeneralResponse\ResponseHeader::class);

        $this->log(LogLevel::DEBUG, 'Start parsing response XML');

        $contents = $response->getContents();

        // parse the data
        $resultData = $sabreService->parse($contents);

        if (!$resultData instanceof DomainInfoResponse\DomainInfoResponse) {
            throw new DynadotApiException('We failed to parse the response');
        }

        $code = $resultData->DomainInfoHeader->ResponseCode ?? $resultData->DomainInfoHeader->SuccessCode;
        if ($code != GeneralResponse\ResponseHeader::RESPONSECODE_OK) {
            throw new DynadotApiException($resultData->DomainInfoHeader->Error);
        }

        if ($resultData->DomainInfoHeader !== null) {
            /**
             * Check if the API call was successful. If not, return the error
             */
            $code = $resultData->DomainInfoHeader->SuccessCode;
            if ($code != DomainInfoResponse\DomainInfoHeader::SUCCESSCODE_OK) {
                throw new DynadotApiException($resultData->DomainInfoHeader->Error);
            }
        }

        $this->log(LogLevel::DEBUG, 'Returning domain info');

        // Here we know our API call was succesful, return the domain info.
        return $resultData->DomainInfoContent->Domain;
    }

    /**
     * Log a message to our logger, if we have any.
     *
     * @param string $level
     * @param string $message
     */
    protected function log(string $level, string $message): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message);
        }
    }

    /**
     * Performs the actual API call (internal method)
     *
     * @param array $requestData
     *
     * @return \Psr\Http\Message\StreamInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     */
    protected function performRawApiCall(array $requestData): StreamInterface
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
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Set nameservers for a domain (max 13). An exception will be thrown in case of an error.
     *
     * @param string $domain The domain where to set the nameservers for.
     * @param array  $nameservers
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     * @throws \Level23\Dynadot\Exception\ApiLimitationExceededException
     * @throws \Level23\Dynadot\Exception\DynadotApiException
     * @throws \Sabre\Xml\ParseException
     */
    public function setNameserversForDomain(string $domain, array $nameservers): void
    {
        $this->log(LogLevel::DEBUG, 'Set ' . sizeof($nameservers) . ' nameservers for domain ' . $domain);
        $requestData = [
            'command' => 'set_ns',
            'domain'  => $domain,
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

        $this->log(LogLevel::DEBUG, 'API call executed, parsing response...');

        // start parsing XML data using Sabre
        $sabreService = new Service();

        // map certain values to objects
        $sabreService->mapValueObject('{}SetNsResponse', SetNsResponse\SetNsResponse::class);
        $sabreService->mapValueObject('{}SetNsHeader', SetNsResponse\SetNsHeader::class);
        $sabreService->mapValueObject('{}Response', GeneralResponse\Response::class);
        $sabreService->mapValueObject('{}ResponseHeader', GeneralResponse\ResponseHeader::class);

        // parse the data
        $resultData = $sabreService->parse($response->getContents());

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
     *
     * @return DomainResponse\Domain[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     * @throws \Level23\Dynadot\Exception\DynadotApiException
     * @throws \Sabre\Xml\ParseException
     */
    public function getDomainList(): array
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
            '{}NameServers'          => function (Reader $reader) {

                $nameservers = [];
                $id          = '';

                $children = (array)$reader->parseInnerTree();

                foreach ($children as $child) {
                    if ($child['name'] == '{}ServerId') {
                        $id = $child['value'];
                    } elseif ($child['name'] == '{}ServerName') {
                        if (!empty($id) && !empty($child['value'])) {
                            $nameserver             = new DomainResponse\NameServer();
                            $nameserver->ServerId   = $id;
                            $nameserver->ServerName = $child['value'];

                            $nameservers[] = $nameserver;
                        }
                        $id = null;
                    }
                }

                return $nameservers;
            },
            '{}DomainInfoList'       => function (Reader $reader) {
                $domains = [];

                $tree = (array)$reader->parseInnerTree();

                foreach ($tree as $item) {
                    foreach ($item['value'] as $domain) {
                        $domains[] = $domain['value'];
                    }
                }

                return $domains;
            },
            '{}Registrant'           => $this->contactIdDeserializer,
            '{}Admin'                => $this->contactIdDeserializer,
            '{}Technical'            => $this->contactIdDeserializer,
            '{}Billing'              => $this->contactIdDeserializer,
            '{}isForSale'            => $this->booleanDeserializer,
            '{}Hold'                 => $this->booleanDeserializer,
            '{}RegistrantUnverified' => $this->booleanDeserializer,
            '{}UdrpLocked'           => $this->booleanDeserializer,
            '{}Disabled'             => $this->booleanDeserializer,
            '{}Locked'               => $this->booleanDeserializer,
            '{}WithAds'              => $this->booleanDeserializer,
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
        $resultData = $sabreService->parse($response->getContents());

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
     *
     * @return GetContactResponse\Contact
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     * @throws \Level23\Dynadot\Exception\DynadotApiException
     * @throws \Sabre\Xml\ParseException
     */
    public function getContactInfo(int $contactId): GetContactResponse\Contact
    {
        $this->log(LogLevel::DEBUG, 'Fetch contact details for id ' . $contactId);

        $requestData = [
            'command'    => 'get_contact',
            'contact_id' => $contactId,
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
        $resultData = $sabreService->parse($response->getContents());

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

    /**
     * @throws \Level23\Dynadot\Exception\DynadotApiException
     * @throws \Sabre\Xml\ParseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     */
    public function setRenewOption(string $domain, string $renewOption): bool
    {
        $this->log(LogLevel::INFO, 'Set auto renew for: ' . $domain . ' to: ' . $renewOption);

        $requestData = [
            'command'      => 'set_renew_option',
            'domain'       => $domain,
            'renew_option' => $renewOption,
        ];

        // perform the API call
        $response = $this->performRawApiCall($requestData);

        $this->log(LogLevel::DEBUG, 'Start parsing result');

        // start parsing XML data using Sabre
        $sabreService = new Service();

        // map certain values to objects
        $sabreService->mapValueObject('{}SetRenewOptionResponse', SetRenewOptionResponse::class);
        $sabreService->mapValueObject('{}SetRenewOptionHeader', SetRenewOptionHeader::class);
        $sabreService->mapValueObject('{}Response', GeneralResponse\Response::class);
        $sabreService->mapValueObject('{}ResponseHeader', GeneralResponse\ResponseHeader::class);

        // parse the data
        $resultData = $sabreService->parse($response->getContents());

        // General error, like incorrect api key
        if ($resultData instanceof GeneralResponse\Response) {
            $code = $resultData->ResponseHeader->ResponseCode;
            if ($code != GeneralResponse\ResponseHeader::RESPONSECODE_OK) {
                throw new DynadotApiException($resultData->ResponseHeader->Error);
            }
        }

        if (!$resultData instanceof SetRenewOptionResponse) {
            throw new DynadotApiException('We failed to parse the response');
        }

        $code = $resultData->SetRenewOptionHeader->SuccessCode;
        if ($code != SetRenewOptionHeader::SUCCESSCODE_OK) {
            throw new DynadotApiException((string)$resultData->SetRenewOptionHeader->Error);
        }

        return true;
    }
}
