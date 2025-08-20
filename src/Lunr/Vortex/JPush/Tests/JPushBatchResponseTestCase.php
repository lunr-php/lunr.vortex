<?php

/**
 * This file contains the JPushBatchResponseTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\JPush\JPushBatchResponse;
use PHPUnit\Framework\MockObject\MockObject;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the JPushBatchResponse class.
 *
 * @covers \Lunr\Vortex\JPush\JPushBatchResponse
 */
abstract class JPushBatchResponseTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of the Requests\Session class.
     * @var Session|MockObject
     */
    protected $http;

    /**
     * Mock instance of the Logger class.
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * Mock instance of the Requests\Response class.
     * @var Response|MockObject
     */
    protected $response;

    /**
     * Instance of the tested class.
     * @var JPushBatchResponse
     */
    protected JPushBatchResponse $class;

    /**
     * Testcase Constructor.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->http = $this->getMockBuilder('WpOrg\Requests\Session')->getMock();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $this->response = $this->getMockBuilder(Response::class)->getMock();
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->http);
        unset($this->logger);
        unset($this->response);
        unset($this->class);

        parent::tearDown();
    }

}

?>
