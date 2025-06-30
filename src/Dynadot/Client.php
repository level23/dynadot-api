<?php

namespace Level23\Dynadot;

use Ramsey\Uuid\Uuid;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Level23\Dynadot\Dto\DtoInterface;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Level23\Dynadot\Exception\ApiException;
use Level23\Dynadot\Dto\DomainListResult;
use Level23\Dynadot\Dto\ContactListResult;
use Level23\Dynadot\Dto\Contact;
use Level23\Dynadot\Dto\NameserverUpdateResult;
use Level23\Dynadot\Dto\RenewOptionResult;
use Level23\Dynadot\Dto\BulkSearchResult;
use Level23\Dynadot\Dto\SearchResult;
use Level23\Dynadot\Dto\DomainRegistrationResult;
use Level23\Dynadot\Dto\DomainRegistrationRequest;
use Level23\Dynadot\Exception\NetworkException;

class Client
{
    /** @var GuzzleClient */
    private GuzzleClient $http;

    /** @var string */
    private string $apiKey;

    /** @var string */
    private string $apiSecret;

    /** @var string */
    private string $apiVersion = 'v1';

    public function __construct(string $apiKey, string $apiSecret)
    {
        $this->apiKey    = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->http      = new GuzzleClient([
            'base_uri' => 'https://api.dynadot.com/restful/' . $this->apiVersion . '/',
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Retrieve detailed information about a single domain.
     *
     * @param string $domainName
     * @return DomainListResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function getDomainInfo(string $domainName): DomainListResult
    {
        /** @var DomainListResult $result */
        $result = $this->request(
            'GET',
            "domains/{$domainName}",
            [],
            DomainListResult::class
        );

        return $result;
    }

    /**
     * Set nameservers for a domain.
     *
     * @param string $domainName
     * @param array<string> $nameservers
     * @throws ApiException
     * @throws NetworkException
     */
    public function setNameservers(string $domainName, array $nameservers): NameserverUpdateResult
    {
        /** @var NameserverUpdateResult $result */
        $result = $this->request(
            'PUT',
            "domains/{$domainName}/nameservers",
            [
                'nameservers_list' => $nameservers,
            ],
            NameserverUpdateResult::class
        );

        return $result;
    }

    /**
     * Retrieve contact information for a given contact ID.
     *
     * @param int $contactId
     * @return Contact
     * @throws ApiException
     * @throws NetworkException
     */
    public function getContactInfo(int $contactId): Contact
    {
        /** @var Contact $result */
        $result = $this->request(
            'GET',
            "contacts/{$contactId}",
            [],
            Contact::class
        );

        return $result;
    }

    /**
     * Retrieve a list of all contacts in your Dynadot account.
     *
     * @return ContactListResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function getContactList(): ContactListResult
    {
        /** @var ContactListResult $result */
        $result = $this->request(
            'GET',
            "contacts",
            [],
            ContactListResult::class
        );

        return $result;
    }

    /**
     * Retrieve a list of all domains in your Dynadot account.
     *
     * This method returns detailed information about all domains including
     * domain names, expiration dates, registration dates, nameservers,
     * status information, and more.
     *
     * @return DomainListResult Contains an array of DomainInfo objects with detailed domain information
     * @throws ApiException When the API returns an error response
     * @throws NetworkException When there's a network communication error
     */
    public function getDomainList(): DomainListResult
    {
        /** @var DomainListResult $result */
        $result = $this->request(
            'GET',
            "domains",
            [],
            DomainListResult::class
        );
        return $result;
    }

    /**
     * Set the renew option for a domain.
     *
     * @param string $domain
     * @param string $renewOption
     * @return RenewOptionResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function setRenewOption(string $domain, string $renewOption): RenewOptionResult
    {
        /** @var RenewOptionResult $result */
        $result = $this->request(
            'PUT',
            "domains/{$domain}/renew_option",
            ['renew_option' => $renewOption],
            RenewOptionResult::class
        );

        return $result;
    }

    /**
     * Search for multiple domains at once.
     *
     * @param array<string> $domains
     * @return BulkSearchResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function bulkSearch(array $domains): BulkSearchResult
    {
        /** @var BulkSearchResult $result */
        $result = $this->request(
            'GET',
            "domains/bulk_search",
            ['domain_name_list' => implode(',', array_map('trim', $domains))],
            BulkSearchResult::class
        );

        return $result;
    }

    /**
     * Search for a domain.
     *
     * @param string $domain
     * @return SearchResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function search(string $domain, bool $showPrice = false, string $currency = 'USD'): SearchResult
    {
        /** @var SearchResult $result */
        $result = $this->request(
            'GET',
            "domains/{$domain}/search",
            [
                'show_price' => $showPrice ? 'true' : 'false',
                'currency'   => $currency,
            ],
            SearchResult::class
        );

        return $result;
    }

    /**
     * Register a new domain.
     *
     * @param string $domainName
     * @param DomainRegistrationRequest $registrationData
     * @return DomainRegistrationResult
     * @throws ApiException
     * @throws NetworkException
     */
    public function registerDomain(string $domainName, DomainRegistrationRequest $registrationData): DomainRegistrationResult
    {
        /** @var DomainRegistrationResult $result */
        $result = $this->request(
            'POST',
            "domains/{$domainName}/register",
            $registrationData->jsonSerialize(),
            DomainRegistrationResult::class
        );

        return $result;
    }

    /**
     * Generic request helper that wraps Guzzle exceptions and hydrates DTOs.
     *
     * @param string $method
     * @param string $path
     * @param array<string, mixed>  $params
     * @param string $dtoClass
     *
     * @return DtoInterface
     * @throws ApiException
     * @throws NetworkException
     */
    private function request(string $method, string $path, array $params, string $dtoClass): DtoInterface
    {
        $requestId = Uuid::uuid4()->toString();
        
        // Prepare request options
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'X-Request-Id'  => $requestId,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ];

        // For GET requests, use params as query parameters
        if ($method === 'GET' && !empty($params)) {
            $options['query'] = $params;
            $payloadJson = '';
        } else {
            $payloadJson = json_encode($params, JSON_UNESCAPED_SLASHES);
            if ($payloadJson === false) {
                throw new InvalidArgumentException('Failed to encode request body as JSON');
            }
            $options['body'] = $payloadJson;
        }

        $stringToSign = implode("\n", [
            $this->apiKey,
            '/' . trim($path, '/'),
            $requestId,
            $payloadJson,
        ]);
        $signature = hash_hmac('sha256', $stringToSign, $this->apiSecret);
        $options['headers']['X-Signature'] = $signature;

        try {
            $response = $this->http->request($method, $path, $options);
        } catch (RequestException $e) {
            if ($e->getHandlerContext()['errno'] ?? null) {
                throw new NetworkException('Network error communicating with Dynadot API', 0, $e);
            }

            $response = $e->getResponse();
            if ($response === null) {
                throw new NetworkException('No response received from Dynadot API', 0, $e);
            }

            throw ApiException::fromResponse($e->getRequest(), $response, $e);
        }

        $data = json_decode($response->getBody()->getContents(), true);

        // check the status code in the json response and throw an exception if it's not 200
        if ($data['code'] >= 400) {
            // Create a request object for the exception
            $request = new Request($method, $path, $options['headers'], $options['body'] ?? null);
            throw ApiException::fromResponse($request, $response);
        }

        if (!is_a($dtoClass, DtoInterface::class, true)) {
            throw new InvalidArgumentException("$dtoClass must implement DtoInterface");
        }

        /** @var DtoInterface $dto */
        $dto = $dtoClass::fromArray($data['data'] ?? []);

        return $dto;
    }
}
