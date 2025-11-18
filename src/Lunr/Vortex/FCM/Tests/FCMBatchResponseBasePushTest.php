<?php

/**
 * This file contains the FCMBatchResponseBasePushTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\FCM\FCMBatchResponse;
use Lunr\Vortex\PushNotificationStatus;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Exception\Transport\Curl as CurlException;

/**
 * This class contains tests for the constructor of the FCMBatchResponse class
 * in case of a push notification error.
 *
 * @covers Lunr\Vortex\FCM\FCMBatchResponse
 */
class FCMBatchResponseBasePushTest extends FCMBatchResponseTestCase
{

    /**
     * Test constructor behavior for push success with single endpoint success.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushSuccessWithSingleSuccess(): void
    {
        $content   = file_get_contents(TEST_STATICS . '/Vortex/fcm/response_single_success.json');
        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $this->response->status_code = 200;
        $this->response->body        = $content;

        $this->logger->expects('warning')->never();

        $responses = [
            'endpoint1' => $this->response,
            'endpoint2' => $this->response,
            'endpoint3' => $this->response,
        ];

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $statuses = [
            'endpoint1' => PushNotificationStatus::Success,
            'endpoint2' => PushNotificationStatus::Success,
            'endpoint3' => PushNotificationStatus::Success,
        ];

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertySame('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Test constructor behavior for push success with single endpoint when precondition failed.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushSuccessWithPreconditionFailWithSingle(): void
    {
        $endpoints = [ 'endpoint1' ];

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $this->response->status_code = 400;

        $context = [
            'endpoint' => 'endpoint1',
            'error'    => 'Invalid argument',
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}', $context);

        $responses = [ $this->response ];

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertySame('statuses', [ 'endpoint1' => PushNotificationStatus::Error ]);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Test constructor behavior for push success with single endpoint when precondition failed.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushSuccessWithPreconditionFailWithMultiple(): void
    {
        $endpoints = [ 'endpoint1', 'endpoint2' ];

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $this->response->status_code = 400;

        $context = [
            'endpoint' => 'endpoint1',
            'error'    => 'Invalid argument',
        ];

        $context1 = [ 'endpoint' => 'endpoint2' ] + $context;

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}', $context);

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}', $context1);

        $responses = [ $this->response ];

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $statuses = [
            'endpoint1' => PushNotificationStatus::Error,
            'endpoint2' => PushNotificationStatus::Error
        ];

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertySame('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Test constructor behavior for error of push notification in case of failed request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorFailedRequestWithSingleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 10; });

        $endpoints = [ 'endpoint1' ];

        $responses = [ 'endpoint1' => new RequestsException('cURL error 10: Request error', 'curlerror', NULL) ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 10: Request error' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::TemporaryError ]);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of failed request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorFailedRequestWithMultipleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 10; });

        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        $responses = [
            'endpoint1' => new RequestsException('cURL error 10: Request error', 'curlerror', NULL),
            'endpoint2' => new RequestsException('cURL error 10: Request error', 'curlerror', NULL),
            'endpoint3' => new RequestsException('cURL error 10: Request error', 'curlerror', NULL),
        ];

        $statuses = [
            'endpoint1' => PushNotificationStatus::TemporaryError,
            'endpoint2' => PushNotificationStatus::TemporaryError,
            'endpoint3' => PushNotificationStatus::TemporaryError,
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 10: Request error' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint2', 'error' => 'cURL error 10: Request error' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint3', 'error' => 'cURL error 10: Request error' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorTimeoutWithSingleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1' ];

        $responses = [ 'endpoint1' => new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL) ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::TemporaryError ]);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorTimeoutWithMultipleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        $responses = [
            'endpoint1' => new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL),
            'endpoint2' => new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL),
            'endpoint3' => new RequestsException('cURL error 28: Request timed out', 'curlerror', NULL),
        ];

        $statuses = [
            'endpoint1' => PushNotificationStatus::TemporaryError,
            'endpoint2' => PushNotificationStatus::TemporaryError,
            'endpoint3' => PushNotificationStatus::TemporaryError,
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint2', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint3', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushCurlErrorEasyWithSingleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1' ];

        $responses = [ 'endpoint1' => new RequestsException('cURL error 28: Request timed out', CurlException::EASY, NULL) ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::TemporaryError ]);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushCurlErrorEasyWithMultipleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        $responses = [
            'endpoint1' => new RequestsException('cURL error 28: Request timed out', CurlException::EASY, NULL),
            'endpoint2' => new RequestsException('cURL error 28: Request timed out', CurlException::EASY, NULL),
            'endpoint3' => new RequestsException('cURL error 28: Request timed out', CurlException::EASY, NULL),
        ];

        $statuses = [
            'endpoint1' => PushNotificationStatus::TemporaryError,
            'endpoint2' => PushNotificationStatus::TemporaryError,
            'endpoint3' => PushNotificationStatus::TemporaryError,
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint2', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint3', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushCurlErrorMultiWithSingleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1' ];

        $responses = [ 'endpoint1' => new RequestsException('cURL error 28: Request timed out', CurlException::MULTI, NULL) ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::TemporaryError ]);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushCurlErrorMultiWithMultipleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        $responses = [
            'endpoint1' => new RequestsException('cURL error 28: Request timed out', CurlException::MULTI, NULL),
            'endpoint2' => new RequestsException('cURL error 28: Request timed out', CurlException::MULTI, NULL),
            'endpoint3' => new RequestsException('cURL error 28: Request timed out', CurlException::MULTI, NULL),
        ];

        $statuses = [
            'endpoint1' => PushNotificationStatus::TemporaryError,
            'endpoint2' => PushNotificationStatus::TemporaryError,
            'endpoint3' => PushNotificationStatus::TemporaryError,
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint2', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint3', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushCurlErrorShareWithSingleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1' ];

        $responses = [ 'endpoint1' => new RequestsException('cURL error 28: Request timed out', CurlException::SHARE, NULL) ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::TemporaryError ]);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of timeout request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushCurlErrorShareWithMultipleEndpoints(): void
    {
        $this->mockFunction('curl_errno', function () { return 28; });

        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        $responses = [
            'endpoint1' => new RequestsException('cURL error 28: Request timed out', CurlException::SHARE, NULL),
            'endpoint2' => new RequestsException('cURL error 28: Request timed out', CurlException::SHARE, NULL),
            'endpoint3' => new RequestsException('cURL error 28: Request timed out', CurlException::SHARE, NULL),
        ];

        $statuses = [
            'endpoint1' => PushNotificationStatus::TemporaryError,
            'endpoint2' => PushNotificationStatus::TemporaryError,
            'endpoint3' => PushNotificationStatus::TemporaryError,
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                        [ 'endpoint' => 'endpoint2', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->logger->expects('warning')
                     ->once()
                     ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint3', 'error' => 'cURL error 28: Request timed out' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of bad request error.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorBadRequestError(): void
    {
        $httpCode  = 400;
        $content   = file_get_contents(TEST_STATICS . '/Vortex/fcm/response_single_error.json');
        $endpoints = [ 'endpoint1' ];

        $this->response->status_code = $httpCode;
        $this->response->body        = $content;

        $responses = [ 'endpoint1' => $this->response ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'The registration token is not a valid FCM registration token' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::InvalidEndpoint ]);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Test constructor behavior for error of push notification in case of bad request error.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorGenericBadRequestError(): void
    {
        $httpCode  = 400;
        $content   = file_get_contents(TEST_STATICS . '/Vortex/fcm/response_generic_error.json');
        $endpoints = [ 'endpoint1' ];

        $this->response->status_code = $httpCode;
        $this->response->body        = $content;

        $responses = [ 'endpoint1' => $this->response ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => 'Invalid Argument' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Error ]);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Unit test data provider.
     *
     * @return array $data array of fcm errors
     */
    public static function errorDataProvider(): array
    {
        $data = [];

        $data[] = [ 'Invalid argument', 400, PushNotificationStatus::Error ];
        $data[] = [ 'Error with authentication', 401, PushNotificationStatus::Error ];
        $data[] = [ 'Mismatched sender', 403, PushNotificationStatus::InvalidEndpoint ];
        $data[] = [ 'Unregistered or missing token', 404, PushNotificationStatus::InvalidEndpoint ];
        $data[] = [ 'Exceeded qouta error', 429, PushNotificationStatus::TemporaryError ];
        $data[] = [ 'Internal error', 500, PushNotificationStatus::TemporaryError ];
        $data[] = [ 'Timeout', 503, PushNotificationStatus::TemporaryError ];
        $data[] = [ 'Unknown error', 440, PushNotificationStatus::Unknown ];

        return $data;
    }

    /**
     * Test constructor behavior for error of push notification.
     *
     * @param string                 $errorMessage Error message.
     * @param int                    $httpCode     Http code of the response.
     * @param PushNotificationStatus $status       The expected status.
     *
     * @dataProvider errorDataProvider
     * @covers       Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorsSingle(string $errorMessage, int $httpCode, PushNotificationStatus $status): void
    {
        $content   = 'stuff';
        $endpoints = [ 'endpoint1' ];

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $this->response->status_code = $httpCode;
        $this->response->body        = $content;

        $responses = [ 'endpoint1' => $this->response ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                         [ 'endpoint' => 'endpoint1', 'error' => $errorMessage ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => $status ]);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Test constructor behavior for error of push notification.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorsMultiple(): void
    {
        $this->response->body = 'stuff';

        $endpoints = [];
        $responses = [];
        $statuses  = [];

        foreach ($this->errorDataProvider() as $key => $value)
        {
            $endpoint = 'endpoint' . $key;

            $endpoints[] = $endpoint;

            // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
            $this->response->status_code = $value[1];

            $responses[$endpoint] = clone $this->response;
            $statuses[$endpoint]  = $value[2];

            $this->logger->expects('warning')
                         ->once()
                         ->with('Dispatching FCM notification failed for endpoint {endpoint}: {error}',
                            [ 'endpoint' => $endpoint, 'error' => $value[0] ]
                         );
        }

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertyEquals('statuses', $statuses);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Test constructor behavior for push success with broadcast success.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushSuccessWithBroadcastSuccess(): void
    {
        $content   = file_get_contents(TEST_STATICS . '/Vortex/fcm/response_single_success.json');
        $endpoints = [];

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $this->response->status_code = 200;
        $this->response->body        = $content;

        $this->logger->expects('warning')->never();

        $responses = [
            $this->response,
        ];

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $broadcastStatus = PushNotificationStatus::Success;

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertySame('broadcastStatus', $broadcastStatus);
        $this->assertPropertySame('responses', $responses);
    }

    /**
     * Test constructor behavior for error of push notification in case of failed request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorFailedRequestWithBroadcast(): void
    {
        $this->mockFunction('curl_errno', function () { return 10; });

        $endpoints = [];

        $responses = [ new RequestsException('cURL error 10: Request error', 'curlerror', NULL) ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM broadcast failed: {error}',
                         [ 'error' => 'cURL error 10: Request error' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertySame('broadcastStatus', PushNotificationStatus::TemporaryError);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Test constructor behavior for error of push notification in case of failed request.
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushErrorUnknownFailedRequestWithBroadcast(): void
    {
        $this->mockFunction('curl_errno', function () { return 10; });

        $endpoints = [];

        $responses = [ new RequestsException('Unknown error', 'error', NULL) ];

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM broadcast failed: {error}',
                         [ 'error' => 'Unknown error' ]
                     );

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertySame('broadcastStatus', PushNotificationStatus::Unknown);
        $this->assertPropertySame('responses', $responses);

        $this->unmockFunction('curl_errno');
    }

    /**
     * Unit test data provider.
     *
     * @return array $data array of fcm errors
     */
    public static function errorBroadcastDataProvider(): array
    {
        $data = [];

        $data[] = [ 'Invalid argument', 400, PushNotificationStatus::Error ];
        $data[] = [ 'Error with authentication', 401, PushNotificationStatus::Error ];
        $data[] = [ 'Exceeded qouta error', 429, PushNotificationStatus::TemporaryError ];
        $data[] = [ 'Internal error', 500, PushNotificationStatus::TemporaryError ];
        $data[] = [ 'Timeout', 503, PushNotificationStatus::TemporaryError ];
        $data[] = [ 'Unknown error', 440, PushNotificationStatus::Unknown ];

        return $data;
    }

    /**
     * Test constructor behavior for push success with broadcast success.
     *
     * @param string                 $errorMessage Error message.
     * @param int                    $httpCode     Http code of the response.
     * @param PushNotificationStatus $status       The expected status.
     *
     * @dataProvider errorBroadcastDataProvider
     * @covers       Lunr\Vortex\FCM\FCMBatchResponse::__construct
     */
    public function testPushWithBroadcastFailures(string $errorMessage, int $httpCode, PushNotificationStatus $status): void
    {
        $endpoints = [];

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $this->response->status_code = $httpCode;
        $this->response->body        = '';

        $this->logger->expects('warning')
                     ->once()
                     ->with(
                         'Dispatching FCM broadcast failed: {error}',
                         [ 'error' => $errorMessage ]
                     );

        $responses = [
            $this->response,
        ];

        $this->class = new FCMBatchResponse($responses, $this->logger, $endpoints);

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertySame('endpoints', $endpoints);
        $this->assertPropertySame('broadcastStatus', $status);
        $this->assertPropertySame('responses', $responses);
    }

}

?>
