<?php

/**
 * This file contains the JPushDispatcherTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\JPush\JPushDispatcher;
use Lunr\Vortex\JPush\JPushPayload;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the JPushDispatcher class.
 *
 * @covers \Lunr\Vortex\JPush\JPushDispatcher
 */
abstract class JPushDispatcherTestCase extends LunrBaseTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Mock instance of the Requests\Session class.
     * @var Session&MockInterface
     */
    protected Session&MockInterface $http;
    /**
     * Mock instance of the Requests\Response class.
     * @var Response&MockObject
     */
    protected Response&MockObject $response;

    /**
     * Mock instance of a Logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Mock instance of the JPush Payload class.
     * @var JPushPayload&MockObject
     */
    protected JPushPayload&MockObject $payload;

    /**
     * Instance of the tested class.
     * @var JPushDispatcher
     */
    protected JPushDispatcher $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->http     = Mockery::mock(Session::class);
        $this->logger   = Mockery::mock(LoggerInterface::class);
        $this->response = $this->getMockBuilder(Response::class)->getMock();
        $this->payload  = $this->getMockBuilder('Lunr\Vortex\JPush\JPushPayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->class = new JPushDispatcher($this->http, $this->logger);

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
        unset($this->payload);
        unset($this->class);

        parent::tearDown();
    }

}

?>
