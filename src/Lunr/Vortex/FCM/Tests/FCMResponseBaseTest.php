<?php

/**
 * This file contains the FCMResponseBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the constructor of the FCMResponse class
 * in case of a push notification error.
 *
 * @covers Lunr\Vortex\FCM\FCMResponse
 */
class FCMResponseBaseTest extends FCMResponseTestCase
{

    /**
     * Test statuses is initialized as an empty array.
     */
    public function testStatusesIsInitializedAsEmptyArray(): void
    {
        $this->assertArrayEmpty($this->getReflectionPropertyValue('statuses'));
    }

    /**
     * Test get_broadcast_status when status is not set.
     *
     * @covers Lunr\Vortex\FCM\FCMResponse::get_broadcast_status
     */
    public function testGetBroadcastStatusWhenNotSet(): void
    {
        $this->assertSame(PushNotificationStatus::Unknown, $this->class->get_broadcast_status());
    }

    /**
     * Test get_broadcast_status when status is set.
     *
     * @covers Lunr\Vortex\FCM\FCMResponse::get_broadcast_status
     */
    public function testGetBroadcastStatusWhenSet(): void
    {
        $this->setReflectionPropertyValue('broadcastStatus', PushNotificationStatus::Success);

        $this->assertSame(PushNotificationStatus::Success, $this->class->get_broadcast_status());
    }

    /**
     * Test add_broadcast_response sets broadcast status.
     *
     * @covers Lunr\Vortex\FCM\FCMResponse::add_broadcast_response
     */
    public function testAddBroadcastResponseSetsStatus(): void
    {
        $this->setReflectionPropertyValue('broadcastStatus', PushNotificationStatus::Unknown);

        $this->batchResponse->expects($this->once())
                            ->method('get_broadcast_status')
                            ->willReturn(PushNotificationStatus::Success);

        $this->class->add_broadcast_response($this->batchResponse);

        $this->assertSame(PushNotificationStatus::Success, $this->getReflectionPropertyValue('broadcastStatus'));
    }

}

?>
