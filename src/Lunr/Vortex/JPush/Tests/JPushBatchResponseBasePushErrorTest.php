<?php

/**
 * This file contains the JPushBatchResponseBasePushErrorTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\JPush\JPushBatchResponse;
use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the constructor of the JPushBatchResponse class
 * in case of a push notification error.
 *
 * @covers \Lunr\Vortex\JPush\JPushBatchResponse
 */
class JPushBatchResponseBasePushErrorTest extends JPushBatchResponseTestCase
{

    /**
     * Test constructor behavior for error of push notification in case of invalid JSON.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorInvalidJSON(): void
    {
        $http_code = 400;
        $content   = 'Field "collapse_key" must be a JSON string: 1463565451';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Invalid request' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Error ]);
    }

    /**
     * Test constructor behavior for error of push notification in case of invalid JSON.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorWithUpstreamMessage(): void
    {
        $http_code = 400;
        $content   = '{"error": {"message": "Field \"collapse_key\" must be a JSON string: 1463565451"}}';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Field "collapse_key" must be a JSON string: 1463565451' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Error ]);
    }

    /**
     * Test constructor behavior for error of push notification in case of an invalid endpoint.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorWithInvalidEndpoint(): void
    {
        $http_code = 400;
        $content   = '{"error": {"code": 1011, "message": "cannot find user by this audience or has been inactive for more than 255 days"}}';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'cannot find user by this audience or has been inactive for more than 255 days' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::InvalidEndpoint ]);
    }

    /**
     * Test constructor behavior for error of push notification in case of authentication error.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorAuthenticationError(): void
    {
        $http_code = 401;
        $content   = 'stuff';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Error with authentication' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Error ]);
    }

    /**
     * Test constructor behavior for error of push notification in case of authentication error.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorConfigError(): void
    {
        $http_code = 403;
        $content   = 'stuff';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Error with configuration' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Error ]);
    }

    /**
     * Unit test data provider for internal error http codes.
     *
     * @return array $data http code
     */
    public static function internalErrorHTTPCodeDataProvider(): array
    {
        $data = [];

        $data[] = [ 500 ];
        $data[] = [ 501 ];
        $data[] = [ 503 ];
        $data[] = [ 599 ];

        return $data;
    }

    /**
     * Test constructor behavior for error of push notification in case of internal error.
     *
     * @param int $http_code HTTP code received
     *
     * @dataProvider internalErrorHTTPCodeDataProvider
     * @covers       \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorInternalError(int $http_code): void
    {
        $content = 'stuff';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Internal error' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::TemporaryError ]);
    }

    /**
     * Unit test data provider for unknown error http codes.
     *
     * @return array $data http code
     */
    public static function unknownErrorHTTPCodeDataProvider(): array
    {
        $data = [];

        $data[] = [ 404 ];
        $data[] = [ 405 ];

        return $data;
    }

    /**
     * Test constructor behavior for error of push notification in case of unknown error.
     *
     * @param int $http_code HTTP code received
     *
     * @dataProvider unknownErrorHTTPCodeDataProvider
     * @covers       \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorUnknownError(int $http_code): void
    {
        $content = 'stuff';

        $this->response->status_code = $http_code;
        $this->response->body        = $content;

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Unknown error' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Unknown ]);
    }

    /**
     * Test constructor behavior for error of push notification in case of unknown error.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushErrorReportingAPI(): void
    {

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Unknown error' ]
                     );

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Unknown ]);
    }

}

?>
