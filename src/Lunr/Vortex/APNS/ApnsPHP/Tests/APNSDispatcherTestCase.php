<?php

/**
 * This file contains the APNSDispatcherTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2016 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\APNS\ApnsPHP\Tests;

use ApnsPHP\Push;
use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\APNS\APNSAlertPayload;
use Lunr\Vortex\APNS\APNSLiveActivityPayload;
use Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the APNSDispatcher class.
 *
 * @covers Lunr\Vortex\APNS\ApnsPHP\APNSDispatcher
 */
abstract class APNSDispatcherTestCase extends LunrBaseTestCase
{

    /**
     * Instance of the tested class.
     * @var APNSDispatcher&MockObject&Stub
     */
    protected APNSDispatcher $class;

    /**
     * Mock instance of a Logger class.
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mock instance of an APNS Push class.
     * @var Push
     */
    protected $apnsPush;

    /**
     * Mock instance of the APNS Payload class.
     * @var APNSAlertPayload&MockObject
     */
    protected APNSAlertPayload&MockObject $alertPayload;

    /**
     * Mock instance of the APNS LA Payload class.
     * @var APNSLiveActivityPayload&MockObject
     */
    protected APNSLiveActivityPayload&MockObject $liveActivityPayload;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $this->apnsPush = $this->getMockBuilder('ApnsPHP\Push')
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->alertPayload = $this->getMockBuilder('Lunr\Vortex\APNS\APNSAlertPayload')->getMock();

        $this->liveActivityPayload = $this->getMockBuilder('Lunr\Vortex\APNS\APNSLiveActivityPayload')->getMock();

        $this->class = new APNSDispatcher($this->logger, $this->apnsPush);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->logger);
        unset($this->apnsPush);
        unset($this->alertPayload);
        unset($this->liveActivityPayload);
        unset($this->class);

        parent::tearDown();
    }

}

?>
