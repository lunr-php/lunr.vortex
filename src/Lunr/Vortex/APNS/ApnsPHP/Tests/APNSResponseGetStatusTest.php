<?php

/**
 * This file contains the APNSResponseGetStatusTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP\Tests;

use Lunr\Vortex\APNS\ApnsPHP\APNSResponse;
use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the get_status function of the APNSResponse class.
 *
 * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse
 */
class APNSResponseGetStatusTest extends APNSResponseTestCase
{

    /**
     * Testcase constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->class = new APNSResponse($this->logger, [], [], [], '{}');

        parent::baseSetUp($this->class);
    }

    /**
     * Unit test data provider.
     *
     * @return array $data array of endpoints statuses / status result
     */
    public static function endpointDataProvider(): array
    {
        $data = [];

        $data['unknown status if no status set'] = [ [], PushNotificationStatus::Unknown ];

        $data['unknown status if endpoint absent']           = [
            [
                'endpoint1' => PushNotificationStatus::InvalidEndpoint,
            ],
            PushNotificationStatus::Unknown,
        ];
        $data['unknown status if endpoint absent, full set'] = [
            [
                'endpoint1' => PushNotificationStatus::Error,
                'endpoint2' => PushNotificationStatus::InvalidEndpoint,
                'endpoint3' => PushNotificationStatus::Success,
            ],
            PushNotificationStatus::Unknown,
        ];

        $data['own status if present']           = [
            [
                'endpoint_param' => PushNotificationStatus::InvalidEndpoint,
            ],
            PushNotificationStatus::InvalidEndpoint,
        ];
        $data['own status if present, full set'] = [
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
     * @covers       Lunr\Vortex\APNS\ApnsPHP\APNSResponse::get_status
     */
    public function testGetStatus(array $statuses, PushNotificationStatus $status): void
    {
        $this->setReflectionPropertyValue('statuses', $statuses);

        $result = $this->class->get_status('endpoint_param');

        $this->assertEquals($status, $result);
    }

}

?>
