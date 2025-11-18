<?php

/**
 * This file contains the JPushDispatcherSetTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Vortex\JPush\Tests;

/**
 * This class contains tests for the setters of the JPushDispatcher class.
 *
 * @covers \Lunr\Vortex\JPush\JPushDispatcher
 */
class JPushDispatcherSetTest extends JPushDispatcherTestCase
{

    /**
     * Test that set_auth_token() sets the endpoint.
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::set_auth_token
     */
    public function testSetAuthTokenSetsPayload(): void
    {
        $this->class->set_auth_token('auth_token');

        $this->assertSame('Basic auth_token', $this->http->headers['Authorization']);
        $this->assertSame('application/json', $this->http->headers['Content-Type']);

        $this->assertPropertyEquals('authToken', 'auth_token');
    }

    /**
     * Test the fluid interface of set_auth_token().
     *
     * @covers \Lunr\Vortex\JPush\JPushDispatcher::set_auth_token
     */
    public function testSetAuthTokenReturnsSelfReference(): void
    {
        $this->assertEquals($this->class, $this->class->set_auth_token('auth_token'));
    }

}

?>
