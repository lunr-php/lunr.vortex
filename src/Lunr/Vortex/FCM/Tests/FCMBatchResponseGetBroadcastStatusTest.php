<?php

/**
 * This file contains the FCMBatchResponseGetBroadcastStatusTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\FCM\FCMBatchResponse;
use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the get_status function of the FCMBatchResponse class.
 *
 * @covers Lunr\Vortex\FCM\FCMBatchResponse
 */
class FCMBatchResponseGetBroadcastStatusTest extends FCMBatchResponseTest
{

    /**
     * Testcase constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $content = file_get_contents(TEST_STATICS . '/Vortex/fcm/response_single_success.json');

        $this->response->status_code = 200;
        $this->response->body        = $content;

        $this->class = new FCMBatchResponse([ $this->response ], $this->logger, []);

        parent::baseSetUp($this->class);
    }

    /**
     * Test the get_broadcast_status() returns Success
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::get_broadcast_status
     */
    public function testGetStatusReturnsSuccess(): void
    {
        $this->assertSame(PushNotificationStatus::Success, $this->class->get_broadcast_status());
    }

    /**
     * Test the get_broadcast_status() returns Unknown
     *
     * @covers Lunr\Vortex\FCM\FCMBatchResponse::get_broadcast_status
     */
    public function testGetStatusReturnsUnknown(): void
    {
        $this->set_reflection_property_value('broadcast_status', PushNotificationStatus::Unknown);

        $this->assertSame(PushNotificationStatus::Unknown, $this->class->get_broadcast_status());
    }

}

?>
