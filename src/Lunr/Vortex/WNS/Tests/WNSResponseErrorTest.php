<?php

/**
 * This file contains the WNSResponseErrorTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\WNS\Tests;

use Lunr\Vortex\PushNotificationStatus;

/**
 * This class contains tests for failed WNS dispatches.
 *
 * @covers \Lunr\Vortex\WNS\WNSResponse
 */
class WNSResponseErrorTest extends WNSResponseTestCase
{

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        parent::setUpError();
    }

    /**
     * Test headers are not set when request failed.
     */
    public function testHeadersIsNull(): void
    {
        $this->assertPropertyUnset('headers');
    }

    /**
     * Test that the status is set as error.
     */
    public function testStatusIsError(): void
    {
        $this->assertEquals(PushNotificationStatus::Error, $this->getReflectionPropertyValue('status'));
    }

    /**
     * Test that the endpoint is set correctly.
     */
    public function testEndpointSetCorrectly(): void
    {
        $this->assertPropertySame('endpoint', 'http://localhost/');
    }

    /**
     * Test that the http code is set from the Response object.
     */
    public function testHttpCodeIsSetCorrectly(): void
    {
        $this->assertFalse($this->getReflectionPropertyValue('httpCode'));
    }

    /**
     * Test that the payload is set correctly.
     */
    public function testPayloadIsSetCorrectly(): void
    {
        $this->assertPropertySame('payload', '<?xml version="1.0" encoding="utf-8"?>');
    }

    /**
     * Test that get_status() returns the dispatch status with correct endpoint.
     *
     * @covers Lunr\Vortex\WNS\WNSResponse::get_status
     */
    public function testGetStatusReturnsStatusForCorrectEndpoint(): void
    {
        $this->assertEquals($this->class->get_status('http://localhost/'), PushNotificationStatus::Error);
    }

    /**
     * Test that get_status() returns unknown status with incorrect endpoint.
     *
     * @covers Lunr\Vortex\WNS\WNSResponse::get_status
     */
    public function testGetStatusReturnsUnknownStatusForIncorrectEndpoint(): void
    {
        $this->assertEquals($this->class->get_status('http://foo/'), PushNotificationStatus::Unknown);
    }

}

?>
