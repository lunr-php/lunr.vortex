<?php

/**
 * This file contains the WNSDispatcherTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

use Lunr\Halo\LunrBaseTestCase;
use Lunr\Vortex\WNS\WNSDispatcher;
use Lunr\Vortex\WNS\WNSType;
use Psr\Log\LoggerInterface;
use WpOrg\Requests\Response;
use WpOrg\Requests\Session;

/**
 * This class contains common setup routines, providers
 * and shared attributes for testing the WNSDispatcher class.
 *
 * @covers Lunr\Vortex\WNS\WNSDispatcher
 */
abstract class WNSDispatcherTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of the Requests\Session class.
     * @var Session
     */
    protected $http;

    /**
     * Mock instance of a Logger class.
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Mock instance of the Requests\Response class.
     * @var Response
     */
    protected $response;

    /**
     * Mock instance of the WNS Payload class.
     * @var WNSPayload
     */
    protected $payload;

    /**
     * Instance of the tested class.
     * @var WNSDispatcher
     */
    protected WNSDispatcher $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->http = $this->getMockBuilder('WpOrg\Requests\Session')->getMock();

        $this->response = $this->getMockBuilder(Response::class)->getMock();

        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();

        $this->payload = $this->getMockBuilder('Lunr\Vortex\WNS\WNSPayload')
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->class = new WNSDispatcher($this->http, $this->logger);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->payload);
        unset($this->http);
        unset($this->logger);
        unset($this->response);

        parent::tearDown();
    }

    /**
     * Unit test data provider for valid WNS Types.
     *
     * @return array $types Array of WNS types.
     */
    public static function validTypeProvider(): array
    {
        $types   = [];
        $types[] = [ WNSType::TILE ];
        $types[] = [ WNSType::TOAST ];
        $types[] = [ WNSType::RAW ];
        $types[] = [ WNSType::BADGE ];

        return $types;
    }

}

?>
