<?php

/**
 * This file contains the JPushReportReportErrorTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

/**
 * This class contains tests for the get_report function of the JPushReport class.
 *
 * @covers \Lunr\Vortex\JPush\JPushReport
 */
class JPushReportReportErrorTest extends JPushReportTest
{

    /**
     * Unit test data provider for errors.
     *
     * @return array Errors
     */
    public function errorProvider(): array
    {
        $return = [];

        $return['http400'] = [ 400, 5, 'Invalid request' ];
        $return['http401'] = [ 401, 5, 'Error with authentication' ];
        $return['http402'] = [ 402, 0, 'Unknown error' ];
        $return['http403'] = [ 403, 5, 'Error with configuration' ];
        $return['http412'] = [ 412, 0, 'Unknown error' ];
        $return['http500'] = [ 500, 2, 'Internal error' ];

        return $return;
    }

    /**
     * Test the report_error() succeeds.
     *
     * @param int    $http_code Endpoint of the notification
     * @param int    $status    Lunr endpoint status
     * @param string $message   Reported message
     *
     * @dataProvider errorProvider
     * @covers       \Lunr\Vortex\JPush\JPushReport::report_error
     */
    public function testReportEndpointErrorSucceeds($http_code, $status, $message): void
    {
        $endpoints = [ 'endpoint1' ];

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Getting JPush notification report failed: {error}', [ 'error' => $message ]);

        $this->response->status_code = $http_code;

        $method = $this->get_accessible_reflection_method('report_error');
        $method->invokeArgs($this->class, [ $this->response, &$endpoints ]);

        $this->assertPropertyEquals('statuses', [ 'endpoint1' => $status ]);
    }

    /**
     * Test the report_error() succeeds with upstream message.
     *
     * @covers \Lunr\Vortex\JPush\JPushReport::report_error
     */
    public function testReportEndpointErrorSucceedsWithUpstreamMessage(): void
    {
        $endpoints = [ 'endpoint1' ];

        $this->logger->expects($this->once())
                     ->method('warning')
                     ->with('Getting JPush notification report failed: {error}', [ 'error' => 'message_id is invalid' ]);

        $this->response->status_code = 400;
        $this->response->body        = '{"error":{"message":"message_id is invalid"}}';

        $method = $this->get_accessible_reflection_method('report_error');
        $method->invokeArgs($this->class, [ $this->response, &$endpoints ]);

        $this->assertPropertyEquals('statuses', [ 'endpoint1' => 5 ]);
    }

}

?>