<?php

/**
 * This file contains the WNSResponseTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\PushNotificationStatus;
use Lunr\Vortex\WNS\WNSResponse;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Response\Headers;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the WNSResponse class.
 *
 * @covers Lunr\Vortex\WNS\WNSResponse
 */
abstract class WNSResponseTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of the Logger class.
     * @var LoggerInterface&MockObject
     */
    protected LoggerInterface&MockObject $logger;

    /**
     * Instance of the tested class.
     * @var WNSResponse
     */
    protected WNSResponse $class;

    /**
     * Testcase Constructor.
     *
     * @return void
     */
    public function setUpError(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $response = $this->getMockBuilder(Response::class)->getMock();

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $response->status_code = FALSE;
        $response->url         = 'http://localhost/';

        $this->class = new WNSResponse($response, $this->logger, '<?xml version="1.0" encoding="utf-8"?>');

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Constructor.
     *
     * @return void
     */
    public function setUpSuccess(): void
    {
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $response = $this->getMockBuilder(Response::class)->getMock();

        $response->headers = new Headers([
            'Date'                         => '2016-01-13',
            'X-WNS-Status'                 => 'received',
            'X-WNS-DeviceConnectionStatus' => 'connected',
            'X-WNS-Error-Description'      => 'Some Error',
            'X-WNS-Debug-Trace'            => 'Some Trace',
        ]);

        // phpcs:ignore Lunr.NamingConventions.CamelCapsVariableName
        $response->status_code = 200;
        $response->url         = 'http://localhost/';

        $this->class = new WNSResponse($response, $this->logger, '<?xml version="1.0" encoding="utf-8"?>');

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->logger);
        unset($this->class);

        parent::tearDown();
    }

    /**
     * Unit test data provider for failed requests.
     *
     * @return array $requests Array of failed request info
     */
    public static function failedRequestProvider(): array
    {
        $requests   = [];
        $requests[] = [ 200, 'channelthrottled', PushNotificationStatus::TemporaryError ];
        $requests[] = [ 200, 'dropped', PushNotificationStatus::ClientError ];
        $requests[] = [ 404, 'N/A', PushNotificationStatus::InvalidEndpoint ];
        $requests[] = [ 410, 'N/A', PushNotificationStatus::InvalidEndpoint ];
        $requests[] = [ 400, 'N/A', PushNotificationStatus::Error ];
        $requests[] = [ 401, 'N/A', PushNotificationStatus::Error ];
        $requests[] = [ 403, 'N/A', PushNotificationStatus::Error ];
        $requests[] = [ 405, 'N/A', PushNotificationStatus::Error ];
        $requests[] = [ 413, 'N/A', PushNotificationStatus::Error ];
        $requests[] = [ 406, 'N/A', PushNotificationStatus::TemporaryError ];
        $requests[] = [ 500, 'N/A', PushNotificationStatus::TemporaryError ];
        $requests[] = [ 503, 'N/A', PushNotificationStatus::TemporaryError ];
        $requests[] = [ 420, 'N/A', PushNotificationStatus::Unknown ];

        return $requests;
    }

}

?>
