<?php

/**
 * This file contains the FCMDispatcherTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\UnencryptedToken;
use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\FCM\FCMDispatcher;
use Lunr\Vortex\FCM\FCMPayload;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the FCMDispatcher class.
 *
 * @covers Lunr\Vortex\FCM\FCMDispatcher
 */
abstract class FCMDispatcherTestCase extends LunrBaseTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Mock instance of the Requests\Session class.
     * @var Session&MockObject
     */
    protected Session&MockObject $http;

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
     * Mock instance of the FCM Payload class.
     * @var FCMPayload&MockInterface
     */
    protected FCMPayload&MockInterface $payload;

    /**
     * Mock instance of the token builder class.
     * @var Builder&MockInterface
     */
    protected Builder&MockInterface $tokenBuilder;

    /**
     * Mock instance of the token UnencryptedToken class.
     * @var UnencryptedToken&MockObject
     */
    protected UnencryptedToken&MockObject $tokenPlain;

    /**
     * Instance of the tested class.
     * @var FCMDispatcher
     */
    protected FCMDispatcher $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->http       = $this->getMockBuilder(Session::class)->getMock();
        $this->response   = $this->getMockBuilder(Response::class)->getMock();
        $this->tokenPlain = $this->getMockBuilder(UnencryptedToken::class)->getMock();

        $this->logger       = Mockery::mock(LoggerInterface::class);
        $this->payload      = Mockery::mock(FCMPayload::class);
        $this->tokenBuilder = Mockery::mock(Builder::class);

        $this->class = new FCMDispatcher($this->http, $this->logger);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->http);
        unset($this->response);
        unset($this->tokenPlain);
        unset($this->logger);
        unset($this->payload);
        unset($this->tokenBuilder);
        unset($this->class);

        parent::tearDown();
    }

}

?>
