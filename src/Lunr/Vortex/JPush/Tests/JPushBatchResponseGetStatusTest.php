<?php

/**
 * This file contains the JPushBatchResponseGetStatusTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\JPush\JPushBatchResponse;
use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the get_status function of the JPushBatchResponse class.
 *
 * @covers \Lunr\Vortex\JPush\JPushBatchResponse
 */
class JPushBatchResponseGetStatusTest extends JPushBatchResponseTestCase
{

    /**
     * Testcase constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $content = file_get_contents(TEST_STATICS . '/Vortex/jpush/response_single_success.json');

        $this->response->status_code = 200;
        $this->response->body        = $content;

        $this->class = new JPushBatchResponse($this->http, $this->logger, $this->response, [ 'endpoint1' ], '[]');

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

        // return deferred status if no status set
        $data[] = [ [], PushNotificationStatus::Deferred ];

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
     * @covers       \Lunr\Vortex\JPush\JPushBatchResponse::get_status
     */
    public function testGetStatus(array $statuses, PushNotificationStatus $status): void
    {
        $this->setReflectionPropertyValue('statuses', $statuses);
        $this->setReflectionPropertyValue('messageID', 1453658564165);

        $result = $this->class->get_status('endpoint_param');

        $this->assertEquals($status, $result);
    }

}

?>
