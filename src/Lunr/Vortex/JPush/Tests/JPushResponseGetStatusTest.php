<?php

/**
 * This file contains the JPushResponseGetStatusTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the get_status function of the JPushResponse class.
 *
 * @covers Lunr\Vortex\JPush\JPushResponse
 */
class JPushResponseGetStatusTest extends JPushResponseTestCase
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
                'endpoint1' => [
                    'status' => PushNotificationStatus::InvalidEndpoint,
                    'batch'  => 165468564
                ],
            ],
            PushNotificationStatus::Unknown,
        ];
        $data[] = [
            [
                'endpoint1' => [
                    'status' => PushNotificationStatus::Error,
                    'batch'  => 165468564
                ],
                'endpoint2' => [
                    'status' => PushNotificationStatus::InvalidEndpoint,
                    'batch'  => 165468564
                ],
                'endpoint3' => [
                    'status' => PushNotificationStatus::Success,
                    'batch'  => 165468564
                ],
            ],
            PushNotificationStatus::Unknown,
        ];

        // return unknown if status was not set
        $data[] = [
            [
                'endpoint_param' => [
                    'batch'  => 165468564
                ],
            ],
            PushNotificationStatus::Unknown,
        ];

        // return endpoint own status if present
        $data[] = [
            [
                'endpoint_param' => [
                    'status' => PushNotificationStatus::InvalidEndpoint,
                    'batch'  => 165468564
                ],
            ],
            PushNotificationStatus::InvalidEndpoint,
        ];
        $data[] = [
            [
                'endpoint1'      => [
                    'status' => PushNotificationStatus::Error,
                    'batch'  => 165468564
                ],
                'endpoint_param' => [
                    'status' => PushNotificationStatus::Success,
                    'batch'  => 165468564
                ],
                'endpoint2'      => [
                    'status' => PushNotificationStatus::InvalidEndpoint,
                    'batch'  => 165468564
                ],
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
     * @covers       Lunr\Vortex\JPush\JPushResponse::get_status
     */
    public function testGetStatus(array $statuses, PushNotificationStatus $status): void
    {
        $this->setReflectionPropertyValue('statuses', $statuses);

        $result = $this->class->get_status('endpoint_param');

        $this->assertEquals($status, $result);
    }

}

?>
