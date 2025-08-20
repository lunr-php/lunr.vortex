<?php

/**
 * This file contains the FCMResponseGetStatusTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the get_status function of the FCMResponse class.
 *
 * @covers Lunr\Vortex\FCM\FCMResponse
 */
class FCMResponseGetStatusTest extends FCMResponseTestCase
{

    /**
     * Unit test data provider.
     *
     * @return array $data array of endpoints statuses / status result
     */
    public static function endpointDataProvider(): array
    {
        $data = [];

        // return unknown status if no status set
        $data[] = [ [], PushNotificationStatus::Unknown ];

        // return unknown status if endpoint absent
        $data[] = [
            [
                'endpoint1' => PushNotificationStatus::InvalidEndpoint,
            ],
            PushNotificationStatus::Unknown,
        ];
        $data[] = [
            [
                'endpoint1' => PushNotificationStatus::Error,
                'endpoint2' => PushNotificationStatus::InvalidEndpoint,
                'endpoint3' => PushNotificationStatus::Success,
            ],
            PushNotificationStatus::Unknown,
        ];

        // return endpoint own status if present
        $data[] = [
            [
                'endpoint_param' => PushNotificationStatus::InvalidEndpoint,
            ],
            PushNotificationStatus::InvalidEndpoint,
        ];
        $data[] = [
            [
                'endpoint1'      => PushNotificationStatus::Error,
                'endpoint_param' => PushNotificationStatus::Success,
                'endpoint2'      => PushNotificationStatus::TemporaryError,
            ],
            PushNotificationStatus::Success,
        ];

        return $data;
    }

    /**
     * Test the get_status() behavior.
     *
     * @param array                  $statuses Endpoints statuses
     * @param PushNotificationStatus $status   Expected function result
     *
     * @dataProvider endpointDataProvider
     * @covers       Lunr\Vortex\FCM\FCMResponse::get_status
     */
    public function testGetStatus(array $statuses, PushNotificationStatus $status): void
    {
        $this->setReflectionPropertyValue('statuses', $statuses);

        $result = $this->class->get_status('endpoint_param');

        $this->assertEquals($status, $result);
    }

}

?>
