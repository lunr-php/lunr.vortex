<?php

/**
 * This file contains the JPushResponseGetMessageIdTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the get_message_id function of the JPushResponse class.
 *
 * @covers Lunr\Vortex\JPush\JPushResponse
 */
class JPushResponseGetMessageIdTest extends JPushResponseTestCase
{

    /**
     * Unit test data provider.
     *
     * @return array $data array of endpoints statuses / status result
     */
    public static function endpointDataProvider(): array
    {
        $data = [];

        // return NULL if endpoint not set
        $data[] = [ [], NULL ];

        // return NULL if endpoint absent
        $data[] = [
            [
                'endpoint1' => [
                    'status'     => PushNotificationStatus::InvalidEndpoint,
                    'message_id' => '165468564'
                ],
            ],
            NULL,
        ];
        $data[] = [
            [
                'endpoint1' => [
                    'status'     => PushNotificationStatus::Error,
                    'message_id' => '165468564'
                ],
                'endpoint2' => [
                    'status'     => PushNotificationStatus::InvalidEndpoint,
                    'message_id' => '165468564'
                ],
                'endpoint3' => [
                    'status'     => PushNotificationStatus::Success,
                    'message_id' => '555165655'
                ],
            ],
            NULL,
        ];

        // return NULL if batch was not set
        $data[] = [
            [
                'endpoint_param' => [
                    'status' => PushNotificationStatus::InvalidEndpoint,
                ],
            ],
            NULL,
        ];

        // return batch if batch is present
        $data[] = [
            [
                'endpoint_param' => [
                    'status'     => PushNotificationStatus::InvalidEndpoint,
                    'message_id' => '165468564'
                ],
            ],
            '165468564',
        ];
        $data[] = [
            [
                'endpoint1'      => [
                    'status'     => PushNotificationStatus::Error,
                    'message_id' => '165468564'
                ],
                'endpoint_param' => [
                    'status'     => PushNotificationStatus::Success,
                    'message_id' => '165468564'
                ],
                'endpoint2'      => [
                    'status'     => PushNotificationStatus::InvalidEndpoint,
                    'message_id' => '555165655'
                ],
            ],
            '165468564',
        ];

        return $data;
    }

    /**
     * Test the get_message_id() behavior.
     *
     * @param array       $statuses Endpoints statuses
     * @param string|null $batch    Expected function result
     *
     * @dataProvider endpointDataProvider
     * @covers       Lunr\Vortex\JPush\JPushResponse::get_message_id
     */
    public function testGetBatch(array $statuses, ?string $batch): void
    {
        $this->setReflectionPropertyValue('statuses', $statuses);

        $result = $this->class->get_message_id('endpoint_param');

        $this->assertEquals($batch, $result);
    }

}

?>
