<?php

/**
 * This file contains the FCMTopicTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Halo\LunrBaseTest;
use Lunr\Vortex\FCM\FCMTopic;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the FCMTopic class.
 *
 * @covers Lunr\Vortex\FCM\FCMTopic
 */
abstract class FCMTopicTest extends LunrBaseTest
{
    /**
     * Mock instance of the Requests\Session class.
     * @var Session&MockObject&Stub
     */
    protected Session&MockObject&Stub $http;

    /**
     * Mock instance of the Requests\Response class.
     * @var Response&MockObject&Stub
     */
    protected Response&MockObject&Stub $response;

    /**
     * Mock instance of a Logger class.
     * @var LoggerInterface&MockObject&Stub
     */
    protected LoggerInterface&MockObject&Stub $logger;

    /**
     * Instance of the tested class.
     * @var FCMTopic
     */
    protected FCMTopic $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->http     = $this->getMockBuilder(Session::class)->getMock();
        $this->response = $this->getMockBuilder(Response::class)->getMock();
        $this->logger   = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $this->class = new FCMTopic($this->http, $this->logger);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->http);
        unset($this->response);
        unset($this->logger);
        unset($this->class);

        parent::tearDown();
    }

}

?>
