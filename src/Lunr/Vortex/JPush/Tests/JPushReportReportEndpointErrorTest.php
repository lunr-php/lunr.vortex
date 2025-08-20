<?php

/**
 * This file contains the JPushReportReportEndpointErrorTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for the get_report function of the JPushReport class.
 *
 * @covers \Lunr\Vortex\JPush\JPushReport
 */
class JPushReportReportEndpointErrorTest extends JPushReportTestCase
{

    /**
     * Unit test data provider for endpoint errors.
     *
     * @return array Endpoint errors
     */
    public static function endpointErrorProvider(): array
    {
        $return = [];

        $return['Unknown failure']                = [
            'endpoint1',
            1,
            PushNotificationStatus::Deferred,
            'Not delivered'
        ];
        $return['Registration ID unknown']        = [
            'endpoint2',
            2,
            PushNotificationStatus::InvalidEndpoint,
            'Registration_id does not belong to the application'
        ];
        $return['Registration ID not in message'] = [
            'endpoint3',
            3,
            PushNotificationStatus::Error,
            'Registration_id belongs to the application, but it is not the target of the message'
        ];
        $return['System failure']                 = [
            'endpoint4',
            4,
            PushNotificationStatus::TemporaryError,
            'The system is abnormal'
        ];
        $return['Failure not matched']            = [
            'endpoint5',
            5,
            PushNotificationStatus::Unknown,
            '5'
        ];

        return $return;
    }

    /**
     * Test the report_endpoint_error() succeeds.
     *
     * @param string                 $endpoint   Endpoint of the notification
     * @param int                    $error_code Error response code
     * @param PushNotificationStatus $status     Lunr status
     * @param string                 $message    Reported message
     *
     * @dataProvider endpointErrorProvider
     * @covers       \Lunr\Vortex\JPush\JPushReport::report_endpoint_error
     */
    public function testReportEndpointErrorSucceeds(string $endpoint, int $error_code, PushNotificationStatus $status, string $message): void
    {
        $log_message = 'Dispatching JPush notification failed for endpoint {endpoint}: {error}';

        $context = [
            'endpoint' => $endpoint,
            'error'    => $message
        ];

        $this->logger->expects('warning')
                     ->once()
                     ->with($log_message, $context);

        $method = $this->getReflectionMethod('report_endpoint_error');
        $method->invokeArgs($this->class, [ $endpoint, $error_code ]);

        $this->assertPropertyEquals('statuses', [ $endpoint => $status ]);
    }

}

?>
