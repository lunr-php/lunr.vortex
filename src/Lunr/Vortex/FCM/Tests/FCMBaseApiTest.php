<?php

/**
 * This file contains the FCMBaseApiTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2024 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\UnencryptedToken;
use Lunr\Halo\LunrBaseTest;
use Lunr\Vortex\FCM\FCMBaseApi;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the FCMBaseApi class.
 *
 * @covers Lunr\Vortex\FCM\FCMBaseApi
 */
abstract class FCMBaseApiTest extends LunrBaseTest
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
     * Mock instance of the token builder class.
     * @var MockInterface<Builder>
     */
    protected Builder&MockInterface $token_builder;

    /**
     * Mock instance of the token UnencryptedToken class.
     * @var MockObject<UnencryptedToken>&stub
     */
    protected UnencryptedToken&MockObject&stub $token_plain;

    /**
     * Instance of the tested class.
     * @var FCMBaseApi&MockObject
     */
    protected FCMBaseApi $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->http     = $this->getMockBuilder(Session::class)->getMock();
        $this->response = $this->getMockBuilder(Response::class)->getMock();
        $this->logger   = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $this->token_builder = Mockery::mock(Builder::class);

        $this->token_plain = $this->getMockBuilder(UnencryptedToken::class)->getMock();

        $this->class = $this->getMockBuilder(FCMBaseApi::class)
                            ->setConstructorArgs([ $this->http, $this->logger ])
                            ->getMockForAbstractClass();

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        Mockery::close();

        unset($this->http);
        unset($this->response);
        unset($this->logger);
        unset($this->class);
        unset($this->token_builder);
        unset($this->token_plain);

        parent::tearDown();
    }

}

?>
