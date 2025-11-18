<?php

/**
 * This file contains the EmailDispatcherTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2014 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\Email\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\Email\EmailDispatcher;
use Lunr\Vortex\Email\EmailPayload;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the EmailDispatcher class.
 *
 * @covers Lunr\Vortex\Email\EmailDispatcher
 */
abstract class EmailDispatcherTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of the PHPMailer class.
     * @var PHPMailer
     */
    protected $mailTransport;

    /**
     * Mock instance of a Logger class.
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mock instance of the Email Payload class.
     * @var EmailPayload
     */
    protected $payload;

    /**
     * Instance of the tested class.
     * @var EmailDispatcher
     */
    protected EmailDispatcher $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->mailTransport = $this->getMockBuilder('PHPMailer\PHPMailer\PHPMailer')->getMock();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $this->payload = $this->getMockBuilder('Lunr\Vortex\Email\EmailPayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->class = new EmailDispatcher($this->mailTransport, $this->logger);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->logger);
        unset($this->mailTransport);
        unset($this->payload);
        unset($this->class);

        parent::tearDown();
    }

}

?>
