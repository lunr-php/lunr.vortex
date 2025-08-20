<?php

/**
 * This file contains the FCMBatchResponseTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2017 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\FCM\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\FCM\FCMBatchResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the FCMBatchResponse class.
 *
 * @covers Lunr\Vortex\FCM\FCMBatchResponse
 */
abstract class FCMBatchResponseTestCase extends LunrBaseTestCase
{

    use MockeryPHPUnitIntegration;

    /**
     * Mock instance of the Logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Mock instance of the Response class.
     * @var Response&MockObject
     */
    protected Response&MockObject $response;

    /**
     * Instance of the tested class.
     * @var FCMBatchResponse
     */
    protected FCMBatchResponse $class;

    /**
     * Testcase Constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->response = $this->getMockBuilder(Response::class)->getMock();
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->logger);
        unset($this->response);
        unset($this->class);

        parent::tearDown();
    }

}

?>
