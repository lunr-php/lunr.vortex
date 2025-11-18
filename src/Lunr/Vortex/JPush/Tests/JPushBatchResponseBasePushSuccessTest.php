<?php

/**
 * This file contains the JPushBatchResponseBasePushSuccessTest class.
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
 * in case of a push notification success.
 *
 * @covers \Lunr\Vortex\JPush\JPushBatchResponse
 */
class JPushBatchResponseBasePushSuccessTest extends JPushBatchResponseTestCase
{

    /**
     * Test constructor behavior for success of push notification with missing results.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushSuccessWithMissingResults(): void
    {
        $content = [];

        $this->response->success = TRUE;
        $this->response->body    = json_encode($content);

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with(
                         'Dispatching JPush notification failed: {error}',
                         [ 'error' => 'Unknown error' ]
                     );

        $this->http->expects($this->never())
                   ->method('post');

        $endpoints = [ 'endpoint1' ];
        $statuses  = [ 'endpoint1' => PushNotificationStatus::Unknown ];

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, $endpoints, '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', $statuses);
    }

    /**
     * Test constructor behavior for push success with single endpoint success.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushSuccessWithSingleSuccess(): void
    {
        $content = [ 'msg_id' => '121654513215' ];

        $this->response->success = TRUE;
        $this->response->body    = json_encode($content);

        $this->logger->expects($this->never())
                     ->method('warning');

        $endpoints = [ 'endpoint1' ];

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, $endpoints, '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', []);
        $this->assertPropertyEquals('messageID', 121654513215);
    }

    /**
     * Test constructor behavior for success of push notification with multiple success.
     *
     * @covers \Lunr\Vortex\JPush\JPushBatchResponse::__construct
     */
    public function testPushSuccessWithMultipleSuccess(): void
    {
        $content = [ 'msg_id' => '121654513215' ];

        $this->response->success = TRUE;
        $this->response->body    = json_encode($content);

        $this->logger->expects($this->never())
                     ->method('warning');

        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3' ];

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, $endpoints, '[]');

        parent::baseSetUp($this->class);

        $this->assertPropertySame('logger', $this->logger);
        $this->assertPropertyEquals('statuses', []);
        $this->assertPropertyEquals('messageID', 121654513215);
    }

}

?>
