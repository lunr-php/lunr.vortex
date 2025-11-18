<?php

/**
 * This file contains the FCMResponseAddBatchResponseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the add_batch_response function of the FCMResponse class.
 *
 * @covers Lunr\Vortex\FCM\FCMResponse
 */
class FCMResponseAddBatchResponseTest extends FCMResponseTestCase
{

    /**
     * Test that add_batch_response() with no endpoint doesn't modify the statuses property.
     *
     * @covers  Lunr\Vortex\FCM\FCMResponse::add_batch_response
     */
    public function testAddBatchResponseWithNoEndpointDoesNotModifyStatuses(): void
    {
        $statuses = [
            'endpoint1' => PushNotificationStatus::Error,
            'endpoint2' => PushNotificationStatus::Success,
        ];

        $this->setReflectionPropertyValue('statuses', $statuses);

        $this->class->add_batch_response($this->batchResponse, []);

        $this->assertPropertySame('statuses', $statuses);
    }

    /**
     * Test that add_batch_response() with endpoints add status for each of them.
     *
     * @covers  Lunr\Vortex\FCM\FCMResponse::add_batch_response
     */
    public function testAddBatchResponseWithEndpointsAddStatus(): void
    {
        $statuses = [
            'endpoint1' => PushNotificationStatus::Error,
            'endpoint2' => PushNotificationStatus::Success,
        ];

        $endpoints = [ 'endpoint2', 'endpoint3', 'endpoint4' ];

        $expectedStatuses = [
            'endpoint1' => PushNotificationStatus::Error,
            'endpoint2' => PushNotificationStatus::InvalidEndpoint,
            'endpoint3' => PushNotificationStatus::Unknown,
            'endpoint4' => PushNotificationStatus::Success,
        ];

        $this->setReflectionPropertyValue('statuses', $statuses);

        $this->batchResponse->expects($this->exactly(3))
                             ->method('get_status')
                             ->willReturnMap(
                                 [
                                     [ 'endpoint2', PushNotificationStatus::InvalidEndpoint ],
                                     [ 'endpoint3', PushNotificationStatus::Unknown ],
                                     [ 'endpoint4', PushNotificationStatus::Success ],
                                 ]
                             );

        $this->class->add_batch_response($this->batchResponse, $endpoints);

        $this->assertPropertySame('statuses', $expectedStatuses);
    }

}

?>
