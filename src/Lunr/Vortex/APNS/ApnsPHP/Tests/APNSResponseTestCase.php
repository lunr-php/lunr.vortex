<?php

/**
 * This file contains the APNSResponseTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP\Tests;

use ApnsPHP\Message;
use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\APNS\ApnsPHP\APNSResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the APNSResponse class.
 *
 * @covers Lunr\Vortex\APNS\ApnsPHP\APNSResponse
 */
abstract class APNSResponseTestCase extends LunrBaseTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Instance of the tested class.
     * @var APNSResponse
     */
    protected APNSResponse $class;

    /**
     * Mock instance of the Logger class.
     * @var LoggerInterface&MockInterface
     */
    protected LoggerInterface&MockInterface $logger;

    /**
     * Mock instance of an APNS Message class.
     * @var Message&MockObject
     */
    protected Message&MockObject $apnsMessage;

    /**
     * Testcase Constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->apnsMessage = $this->getMockBuilder(Message::class)
                                  ->disableOriginalConstructor()
                                  ->getMock();
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

}

?>
