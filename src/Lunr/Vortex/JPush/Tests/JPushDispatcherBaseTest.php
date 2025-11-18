<?php

/**
 * This file contains the JPushDispatcherBaseTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

use Lunr\Halo\PropertyTraits\PsrLoggerTestTrait;
use WpOrg\Requests\Response;

/**
 * This class contains test for the constructor of the JPushDispatcher class.
 *
 * @covers \Lunr\Vortex\JPush\JPushDispatcher
 */
class JPushDispatcherBaseTest extends JPushDispatcherTestCase
{

    use PsrLoggerTestTrait;

    /**
     * Test that the passed Requests\Session object is set correctly.
     */
    public function testRequestsSessionIsSetCorrectly(): void
    {
        $this->assertPropertySame('http', $this->http);
    }

    /**
     * Test that the auth token is set to an empty string by default.
     */
    public function testAuthTokenIsEmptyString(): void
    {
        $this->assertPropertyEquals('authToken', '');
    }

    /**
     * Test get_new_response_object_for_failed_request().
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::get_new_response_object_for_failed_request
     */
    public function testGetNewResponseObjectForFailedRequest(): void
    {
        $method = $this->getReflectionMethod('get_new_response_object_for_failed_request');

        $result = $method->invoke($this->class);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('https://api.jpush.cn/v3/push', $result->url);
    }

    /**
     * Test that get_response() returns JPushResponseObject.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::get_response
     */
    public function testGetResponseReturnsJPushResponseObject(): void
    {
        $result = $this->class->get_response();

        $this->assertInstanceOf('Lunr\Vortex\JPush\JPushResponse', $result);
    }

    /**
     * Test that get_batch_response() returns JPushBatchResponse.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::get_batch_response
     */
    public function testGetBatchResponseReturnsJPushBatchResponseObject(): void
    {
        $this->logger->expects('warning')
                     ->once()
                     ->withAnyArgs();

        $method = $this->getReflectionMethod('get_batch_response');
        $result = $method->invokeArgs($this->class, [ $this->response, [ 'endpoint' ], '[]' ]);

        $this->assertInstanceOf('Lunr\Vortex\JPush\JPushBatchResponse', $result);
    }

}

?>
