<?php

/**
 * This file contains the JPushReportGetReportTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Vortex\PushNotificationStatus;
use WpOrg\Requests\Exception as RequestsException;
use WpOrg\Requests\Exception\Http\Status400 as RequestsExceptionHTTP400;

/**
 * This class contains tests for the get_report function of the JPushReport class.
 *
 * @covers \Lunr\Vortex\JPush\JPushReport
 */
class JPushReportGetReportTest extends JPushReportTestCase
{

    /**
     * Test the get_report() returns when http request fails
     *
     * @covers \Lunr\Vortex\JPush\JPushReport::get_report
     */
    public function testGetReportReturnsWhenHttpRequestFails(): void
    {
        $this->mockMethod([ $this->class, 'report_error' ], function ($response) { echo $response->status_code; });

        $this->setReflectionPropertyValue('auth_token', 'auth_token_24412');

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic auth_token_24412',
        ];

        $this->response->status_code = 400;

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://report.jpush.cn/v3/status/message', $headers, '{"msg_id":1453658564165,"registration_ids":["endpoint1"]}', [])
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status')
                       ->willThrowException(new RequestsExceptionHTTP400(NULL, $this->response));

        $this->expectOutputString('400');

        $this->class->get_report(1453658564165, [ 'endpoint1' ]);

        $this->assertPropertyEquals('statuses', []);

        $this->unmockMethod([ $this->class, 'report_error' ]);
    }

    /**
     * Test get_report() when the curl request fails.
     *
     * @covers \Lunr\Vortex\JPush\JPushReport::get_report
     */
    public function testGetReportWithCurlErrors(): void
    {
        $this->setReflectionPropertyValue('auth_token', 'auth_token_24412');

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic auth_token_24412',
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://report.jpush.cn/v3/status/message', $headers, '{"msg_id":1453658564165,"registration_ids":["endpoint1"]}', [])
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status')
                       ->willThrowException(new RequestsException('cURL error 0001: Network error', 'curlerror', NULL));

        $context = [
            'error' => 'cURL error 0001: Network error',
        ];

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Getting JPush notification report failed: {error}', $context);

        $this->class->get_report(1453658564165, [ 'endpoint1' ]);

        $this->assertPropertyEquals('statuses', [ 'endpoint1' => PushNotificationStatus::Error ]);
    }

    /**
     * Test the get_report() behavior to fetch new statuses.
     *
     * @covers \Lunr\Vortex\JPush\JPushReport::get_report
     */
    public function testGetReportWillFetchUpstreamMixedErrorSuccess(): void
    {
        $endpoints = [ 'endpoint1', 'endpoint2', 'endpoint3', 'endpoint4', 'endpoint5', 'endpoint6', 'endpoint7' ];

        $report_content  = '{"endpoint1": {"status":1},"endpoint2": {"status":2},"endpoint3": {"status":3},';
        $report_content .= '"endpoint4": {"status":4},"endpoint5": {"status":5},"endpoint6": {"status":6},';
        $report_content .= '"endpoint7": {"status":0}}';

        $this->response->success = TRUE;
        $this->response->body    = $report_content;

        $this->setReflectionPropertyValue('auth_token', 'auth_token_24412');

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic auth_token_24412',
        ];

        $request_body = [
            'msg_id'           => 1453658564165,
            'registration_ids' => [
                'endpoint1',
                'endpoint2',
                'endpoint3',
                'endpoint4',
                'endpoint5',
                'endpoint6',
                'endpoint7',
            ],
        ];

        $this->http->expects($this->once())
                   ->method('post')
                   ->with('https://report.jpush.cn/v3/status/message', $headers, json_encode($request_body), [])
                   ->willReturn($this->response);

        $this->response->expects($this->once())
                       ->method('throw_for_status');

        $log_message = 'Dispatching JPush notification failed for endpoint {endpoint}: {error}';
        $this->logger->expects($this->exactly(6))
                     ->method('warning')
                     ->withConsecutive(
                        [
                            $log_message,
                            [
                                'endpoint' => 'endpoint1',
                                'error'    => 'Not delivered'
                            ],
                        ],
                        [
                            $log_message,
                            [
                                'endpoint' => 'endpoint2',
                                'error'    => 'Registration_id does not belong to the application'
                            ],
                        ],
                        [
                            $log_message,
                            [
                                'endpoint' => 'endpoint3',
                                'error'    => 'Registration_id belongs to the application, but it is not the target of the message'
                            ],
                        ],
                        [
                            $log_message,
                            [
                                'endpoint' => 'endpoint4',
                                'error'    => 'The system is abnormal'
                            ],
                        ],
                        [
                            $log_message,
                            [
                                'endpoint' => 'endpoint5',
                                'error'    => 5
                            ],
                        ],
                        [
                            $log_message,
                            [
                                'endpoint' => 'endpoint6',
                                'error'    => 6
                            ],
                        ],
                     );

        $this->class->get_report(1453658564165, $endpoints);

        $this->assertPropertyEquals('statuses', [
            'endpoint1' => PushNotificationStatus::Deferred,
            'endpoint2' => PushNotificationStatus::InvalidEndpoint,
            'endpoint3' => PushNotificationStatus::Error,
            'endpoint4' => PushNotificationStatus::TemporaryError,
            'endpoint5' => PushNotificationStatus::Unknown,
            'endpoint6' => PushNotificationStatus::Unknown,
            'endpoint7' => PushNotificationStatus::Success,
        ]);
    }

}

?>
