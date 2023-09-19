<?php

namespace Level23\Dynadot\Tests\ApiTests;

use Level23\Dynadot\DynadotApi;
use Level23\Dynadot\Exception\DynadotApiException;

class SetRenewOptionTest extends TestCase
{
    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sabre\Xml\ParseException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     */
    public function testFailedResponse(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('renewOptionFailedResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->expectException(DynadotApiException::class);

        $api->setRenewOption('example.com', 'auto');
    }

    public function testInvalidResponse(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('renewOptionInvalidResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->expectException(DynadotApiException::class);
        $this->expectExceptionMessage('We failed to parse the response');

        $api->setRenewOption('example.com', 'auto');
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sabre\Xml\ParseException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     */
    public function testInvalidGeneralResponse(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('invalidApiKeyResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $this->expectException(DynadotApiException::class);

        $api->setRenewOption('example.com', 'auto');
    }

    /**
     * @throws \Level23\Dynadot\Exception\DynadotApiException
     * @throws \Sabre\Xml\ParseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Dynadot\Exception\ApiHttpCallFailedException
     */
    public function testSuccessResponse(): void
    {
        $api = new DynadotApi('_API_KEY_GOES_HERE_');

        $mockHandler = $this->getMockedResponse('renewOptionSuccessResponse.txt');

        $api->setGuzzleOptions(['handler' => $mockHandler]);

        $result  = $api->setRenewOption('example.com', 'auto');

        $this->assertTrue($result);
    }
}